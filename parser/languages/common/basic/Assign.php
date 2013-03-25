<?php

namespace sylma\parser\languages\common\basic;
use sylma\core, sylma\parser\languages\common;

class Assign extends Controled implements common\argumentable, common\addable {

  protected $to;
  protected $value;
  protected $sPrefix = '';

  public function __construct(common\_window $window, $variable, $value, $sPrefix = '') {

    $this->setControler($window);

    $this->to = $variable;
    $this->value = $window->checkContent($value);
    $this->sPrefix = $sPrefix;
  }

  protected function getPrefix() {

    return $this->sPrefix;
  }

  public function onAdd() {

    $this->getControler()->loadContent($this->getValue());
  }

  protected function getValue() {

    return $this->value;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'assign' => array(
        'variable' => $this->to,
        'value' => $this->value,
        'prefix' => $this->getPrefix(),
      )));
  }
}