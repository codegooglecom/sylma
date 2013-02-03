<?php

namespace sylma\parser\reflector\basic;
use sylma\core, sylma\dom;

/**
 *
 */
class Component extends Master {

  protected $element;

  protected static $sFactoryFile = '/core/factory/Cached.php';
  protected static $sFactoryClass = '\sylma\core\factory\Cached';

  public function __construct(domed $parent, dom\element $el, core\argument $arg = null, $bComponent = null, $bForeign = null, $bUnknown = null) {

    $this->setParent($parent);
    $this->setElement($el);
    $this->setArguments($arg);

    $this->setNamespace($el->getNamespace());

    $this->allowComponent($bComponent);
    $this->allowForeign($bForeign);
    $this->allowUnknown($bUnknown);

    $this->parseRoot($el);
  }

  protected function getWindow() {

    return $this->getParent()->getWindow();
  }

  public function parseRoot(dom\element $el) {

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

  protected function getElement() {

    return $this->element;
  }

  protected function setElement(dom\element $element) {

    $this->element = $element;
  }

  protected function parseElementForeign(dom\element $el) {

    parent::parseElementForeign($el);
  }

  protected function throwException($sMessage, $mSender = array(), $iOffset = 2) {

    //$mSender[] = $this->getElement()->asToken();
    parent::throwException($sMessage, $mSender, $iOffset);
  }
}

