<?php

namespace sylma\template\cached;
use sylma\core;

class Attribute {

  protected $sName;
  protected $aValues;

  public function __construct($sName, array $aValues = array()) {

    $this->setName($sName);
    $this->setValues($aValues);
  }

  protected function setName($sName) {

    $this->sName = $sName;
  }

  protected function getName() {

    return $this->sName;
  }

  protected function setValues(array $aValues) {

    $this->aValues = $aValues;
  }

  protected function getValues() {

    return $this->aValues;
  }

  public function addToken($sValue) {

    $this->aValues[] = $sValue;
  }

  public function __toString() {

    return $this->getValues() ? $this->getName() . '="' . implode(' ', $this->getValues()) . '"' : '';
  }
}

