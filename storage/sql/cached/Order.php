<?php

namespace sylma\storage\sql\cached;
use sylma\core;

class Order extends core\module\Managed {

  protected $sElement = '';
  protected $aForeigns = array();
  protected $bDir = true; // true = asc and false = desc

  public function __construct($sElement) {

    if ($sElement && $sElement{0} == '!') {

      $sElement = substr($sElement, 1);
      $this->setDir(false);
    }

    $this->setElement($sElement);
  }

  protected function setDir($bVal) {

    $this->bDir = $bVal;
  }

  protected function getDir() {

    return $this->bDir ? '' : ' DESC';
  }

  protected function setElement($sElement) {

    $this->sElement = $sElement;
  }

  public function setForeigns(array $aForeigns) {

    $this->aForeigns = $aForeigns;
  }

  protected function getForeign($sName) {

    return isset($this->aForeigns[$sName]) ? $this->aForeigns[$sName] : null;
  }

  protected function getElement() {

    return $this->sElement;
  }

  public function __toString() {

    if (!$sValue = $this->getForeign($this->getElement())) {

      $sValue = '`' . $this->getElement() . '`';

    } else if (!preg_match('/[\-a-z0-9\_A-Z_]/', $sValue)) {

      $this->launchException('Uncompatible field name, possible attack');
    }

    return $this->getElement() ? ' ORDER BY ' . $sValue . $this->getDir(): '';
  }
}

