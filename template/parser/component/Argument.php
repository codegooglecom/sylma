<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template\parser;

class Argument extends Variable implements parser\component, common\arrayable {

  protected $sName;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->loadName();

    $this->allowForeign(true);
  }

  public function setContent($mContent) {

    $result = parent::setContent($this->getWindow()->parseArrayables(array($mContent)));
    //$result = $this->loadVar($mContent);
    return $result;
  }

  public function getDefault() {

    if ($sDefault = $this->readx('@default')) {

      $result = $this->getTemplate()->applyPath($sDefault, '');
    }
    else if ($this->getNode()->hasChildren()) {

      $result = $this->parseComponentRoot($this->getNode());
    }
    else {

      $this->launchException("Argument '{$this->getName()}' is missing");
    }

    return $result;
  }

  public function asArray() {

    return array();
  }
}

