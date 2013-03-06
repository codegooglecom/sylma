<?php

namespace sylma\parser\reflector\component;
use sylma\core, sylma\dom, sylma\parser\reflector;

class Foreigner extends reflector\basic\Foreigner implements reflector\component {

  protected static $sFactoryFile = '/core/factory/Cached.php';
  protected static $sFactoryClass = '\sylma\core\factory\Cached';

  protected $allowComponent = false;
  protected $parser;

  public function __construct(reflector\domed $parser, core\argument $arg = null, array $aNamespaces = array()) {

    //$this->allowComponent($bComponent);
    //$this->allowForeign($bForeign);
    //$this->allowUnknown($bUnknown);

    $this->setParser($parser);
    $this->setArguments($arg);

    $this->setUsedNamespaces($aNamespaces);

    //$this->setNamespace($el->getNamespace());
    //$this->parseRoot($el);
  }

  public function parseRoot(dom\element $el) {

    $this->throwException('No root instructions');
    //return $this->parseComponentRoot($el);
  }

  protected function lookupParserForeign($sNamespace) {

    if (!$result = $this->getParser()->lookupParser($sNamespace)) {

      $result = $this->getParser()->createParser($sNamespace);
    }

    return $result;
  }

  protected function parseComponent(dom\element $el) {

    if ($this->allowComponent()) {

      $result = $this->loadComponent($el->getName(), $el, $this->getParser());
    }
    else {

      $result = $this->getParser()->parseComponent($el, $this->getParser());
    }

    return $result;
  }

  protected function loadComponent($sName, dom\element $el, $manager) {

    if (!$this->allowComponent()) {

      $result = $this->getParser()->loadComponent($sName, $el);
    }
    else {

      $result = parent::loadComponent($sName, $el, $this->getParser());
    }

    return $result;
  }

  protected function loadSimpleComponent($sName, $manager = null) {

    if (!$this->allowComponent()) {

      $result = $this->getParser()->loadSimpleComponent($sName);
    }
    else {

      $result = parent::loadSimpleComponent($sName, $this->getParser());
    }

    return $result;
  }

  protected function setParser(reflector\domed $parent) {

    $this->parser = $parent;
  }

  protected function getParser() {

    return $this->parser;
  }

  protected function parseComponentRoot(dom\element $el) {

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

    /**
   * @return \sylma\parser\languages\common\_window
   */
  protected function getWindow() {

    return $this->getParser()->getWindow();
  }

  public function getSourceDirectory($sPath = '') {

    return $this->getParser()->getSourceDirectory($sPath);
  }

  public function getSourceFile($sPath = '') {

    return $this->getParser()->getSourceFile($sPath);
  }
}

