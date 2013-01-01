<?php

namespace sylma\parser\languages\js\basic\instance;
use sylma\parser\languages\js, sylma\parser\languages\common;

class _Array extends _Object implements \Iterator {

  private $aValues;

  public function __construct(common\_window $window, array $aValues = array()) {

    $this->setControler($window);
    $this->setInterface('Array');

    $this->aValues = $aValues;
  }

  public function set($sKey, $mValue) {

    $this->aValues[$sKey] = $mValue;
  }

  public function setContent(array $aValues) {

    $this->aValues = $aValues;
  }

  public function rewind() {

    reset($this->aValues);
  }

  public function current() {

    return current($this->aValues);
  }

  public function key() {

    return key($this->aValues);
  }

  public function next() {

    next($this->aValues);
  }

  public function valid() {

    $sKey = key($this->aValues);

    return $sKey !== NULL && $sKey !== FALSE;
  }

  protected function loadValues() {

    $window = $this->getControler();
    $aResult = array();

    foreach ($this->aValues as $mKey => $mVal) {

      $aResult[] = array(
        '@key' => $mKey,
        $window->argToInstance($mVal),
      );
    }

    return $aResult;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'array' => array('#item' =>  $this->loadValues()),
    ));
  }
}
