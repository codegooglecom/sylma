<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template\parser;

class Argument extends Variable implements parser\component, common\arrayable {

  protected $sName;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->loadName();
  }

  public function setContent($mContent) {

    $result = $this->loadVar($mContent);
    return $result;
  }

  public function getDefault() {

    return $this->getTemplate()->applyPath($this->readx('@default', true), '');
  }

  public function asArray() {

    return array();
  }
}

