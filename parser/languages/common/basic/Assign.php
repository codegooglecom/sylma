<?php

namespace sylma\parser\languages\common\basic;
use sylma\core, sylma\parser\languages\common;

class Assign extends Controled implements common\argumentable {

  protected $to;
  protected $value;

  public function __construct(common\_window $window, $variable, $value) {

    $this->to = $variable;
    $this->value = $value;
    $this->setControler($window);

    $this->value = $window->checkContent($value);
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'assign' => array(
        'variable' => $this->to,
        'value' => $this->value,
      )));
  }
}