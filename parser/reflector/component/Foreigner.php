<?php

namespace sylma\parser\reflector\component;
use sylma\core, sylma\dom, sylma\parser\reflector;

class Foreigner extends reflector\basic\Foreigner implements reflector\component {

  protected static $sFactoryFile = '/core/factory/Cached.php';
  protected static $sFactoryClass = '\sylma\core\factory\Cached';

  protected $allowComponent = true;
  protected $parser;

  public function __construct(reflector\domed $parser, dom\element $el, core\argument $arg = null, $bComponent = null, $bForeign = null, $bUnknown = null) {

    $this->allowComponent($bComponent);
    $this->allowForeign($bForeign);
    $this->allowUnknown($bUnknown);

    $this->setParser($parser);
    $this->setArguments($arg);

    $this->setNamespace($el->getNamespace());
    $this->parseRoot($el);
  }

  protected function parseComponent(dom\element $el) {

    if ($this->allowComponent()) {

      $result = $this->createComponent($el, $this->getParser());
    }
    else {

      $result = $this->getParser()->parseComponent($el, $this->getParser());
    }

    return $result;
  }

  protected function setParser(reflector\domed $parent) {

    $this->parent = $parent;
  }

  protected function getParser() {

    return $this->parent;
  }

  public function getRoot() {

    return $this->getParent()->getRoot();
  }

  protected function parseRoot(dom\element $el) {

    $children = $el->getChildren();

    if (!$children->length) {

      $this->throwException('Empty component not allowed');
    }

    if ($children->length > 1) {

      $mResult = $this->parseChildren($children);
    }
    else {

      $mResult = $this->parseNode($el->getFirst());
    }

    return $mResult;
  }

  protected function parseElementForeign(dom\element $el) {

    parent::parseElementForeign($el);
  }
}

