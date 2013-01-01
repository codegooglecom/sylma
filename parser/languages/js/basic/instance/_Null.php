<?php

namespace sylma\parser\languages\js\basic\instance;
use sylma\parser\languages\js, sylma\parser\languages\common;

class _Null extends _Object {

  public function __construct(common\_window $window) {

    $this->setControler($window);
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array('null' => array()));
  }
}
