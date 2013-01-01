<?php

namespace sylma\parser\languages\js\basic\instance;
use sylma\core, sylma\parser\languages\common;

class _String extends _Object {

  private $sValue = '';

  public function __construct(common\_window $window, $sValue = '') {

    $this->setControler($window);
    $this->setInterface('String');

    $this->sValue = $sValue;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'string' => str_replace('\'', '\\\'', $this->sValue),
    ));
  }
}