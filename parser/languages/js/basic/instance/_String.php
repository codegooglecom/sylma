<?php

namespace sylma\parser\languages\js\basic\instance;
use sylma\core, sylma\parser\languages\common;

class _String extends _Object {

  private $value  = '';

  public function __construct(common\_window $window, $value = null) {

    $this->setControler($window);
    $this->setInterface('String');

    $this->setValue($value);
  }

  protected function setValue($value) {

    $this->value = $value;
  }

  protected function getValue() {

    return $this->value;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'string' => (is_string($this->getValue()) ? str_replace('\'', '\\\'', $this->getValue()) : $this->getValue()),
    ));
  }
}