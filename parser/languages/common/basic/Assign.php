<?php

namespace sylma\parser\languages\common\basic;
use sylma\core, sylma\parser\languages\common;

class Assign extends Controled implements common\argumentable, common\addable {

  protected $target;
  protected $value;
  protected $sPrefix = '';

  public function __construct(common\_window $window, $variable, $mValue, $sPrefix = '') {

    $this->setControler($window);

    $this->target = $variable;
    $this->setPrefix($sPrefix);
    $this->setValue($mValue);
  }

  protected function setPrefix($sPrefix) {

    $this->sPrefix = $sPrefix;
  }

  protected function getTarget() {

    return $this->target;
  }

  protected function setValue($mValue) {

    if (is_array($mValue) && $this->getPrefix() == '.') {

      $this->value = $this->getWindow()->toString($mValue, $this->getTarget());
    }
    else {

      $this->value = $mValue;
    }
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
        'variable' => $this->getTarget(),
        'value' => $this->getValue(),
        'prefix' => $this->getPrefix(),
      )));
  }
}