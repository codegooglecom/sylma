<?php

namespace sylma\parser\languages\php\basic\instance;
use sylma\parser\languages\php, sylma\parser\languages\common;

require_once('_Scalar.php');
require_once('parser/languages/common/_instance.php');

class _Array extends _Scalar implements common\_instance, \Iterator {

  private $aValues;

  public function __construct(common\_window $controler, array $aValues = array()) {

    $this->setControler($controler);
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

  public function asArgument() {

    $window = $this->getControler();
    $aContent = array();

    foreach ($this->aValues as $mKey => $mVal) {

      $aContent[] = array(
        '@key' => $mKey,
        $window->argToInstance($mVal),
      );
    }

    return $this->getControler()->createArgument(array(
      'array' => array('#item' =>  $aContent),
    ));
  }
}
