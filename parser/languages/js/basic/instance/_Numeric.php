<?php

namespace sylma\parser\languages\js\basic\instance;
use \sylma\core, \sylma\parser\languages\common, \sylma\parser\languages\js;

class _Numeric extends _Object {

  private $mValue = '';

  public function __construct(common\_window $window, $mValue) {

    $this->setControler($window);
    $this->setInterface('Numeric');

    $this->mValue = $mValue;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'numeric' => $this->mValue,
    ));
  }
}