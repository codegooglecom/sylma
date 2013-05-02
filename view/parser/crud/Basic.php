<?php

namespace sylma\view\parser\crud;
use sylma\core, sylma\dom, sylma\parser\reflector;

class Basic extends reflector\component\Foreigner {

  protected $sAlias = '';
  
  protected function loadAlias() {

    $this->setAlias($this->readx('@name'));
  }

  protected function setAlias($sValue) {

    $this->sAlias = $sValue;
  }

  public function getAlias() {

    return $this->sAlias;
  }
}

