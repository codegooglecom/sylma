<?php

namespace sylma\parser\action\php\basic\instance;
use sylma\parser\action\php;

require_once('_Scalar.php');
require_once(dirname(dirname(__dir__)) . '/_instance.php');

class _Array extends _Scalar implements php\_instance, \Iterator {

  private $aValues;

  public function __construct(php\_window $controler, array $aValues = array()) {

    $this->setControler($controler);
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
        'key' => $window->argToInstance($mKey),
        'value' => $mVal,
      );
    }

    return $this->getControler()->createArgument(array(
      'array' => array('#item' =>  $aContent),
    ));
  }
}
