<?php

namespace sylma\parser\languages\common\basic;
use sylma\core, sylma\parser\languages\common;

class _Break extends Controled implements common\argumentable, common\instruction {

  public function __construct(common\_window $window) {

    $this->setWindow($window);
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'break' => array(),
    ));
  }
}

