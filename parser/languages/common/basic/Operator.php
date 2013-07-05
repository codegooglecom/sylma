<?php

namespace sylma\parser\languages\common\basic;
use \sylma\core, \sylma\parser\languages\common;

class Operator extends Controled implements common\argumentable, common\operator {

  protected $sValue = '';

  public function __construct(common\_window $window, $sValue) {

    $this->setValue($sValue);
    $this->setControler($window);
  }

  protected function setValue($sValue) {

    $this->sValue = $sValue;
  }

  public function getValue() {

    return $this->sValue;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'operator' => $this->getValue(),
    ));
  }

  public function __toString() {

    return $this->getValue();
  }
}