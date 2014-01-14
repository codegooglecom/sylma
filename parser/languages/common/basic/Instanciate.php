<?php

namespace sylma\parser\languages\common\basic;
use sylma\core, sylma\parser\languages\common;

class Instanciate extends Controled implements common\_object, common\_instance, common\argumentable {

  protected $instance;
  protected $aArguments = array();

  public function __construct(common\_window $controler, common\_instance $instance, array $aArguments = array()) {

    $this->setControler($controler);

    $this->instance = $instance;
    $this->setArguments($aArguments);
  }

  public function getInterface() {

    return $this->instance->getInterface();
  }

  protected function setArguments(array $aArguments) {

    $this->aArguments = $aArguments;
  }

  protected function getArguments() {

    return $this->aArguments;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'instanciate' => array(
        '@class' => $this->getInterface(),
        '#argument' => $this->getArguments(),
    )));
  }
}