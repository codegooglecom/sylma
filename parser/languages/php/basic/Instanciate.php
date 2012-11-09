<?php

namespace sylma\parser\languages\php\basic;
use sylma\parser\languages\common, sylma\parser\languages\php, sylma\core;

class Instanciate extends common\basic\Controled implements common\_object, common\_instance, common\argumentable {

  protected $instance;
  protected $aArguments = array();

  public function __construct(common\_window $controler, common\_instance $instance, array $aArguments = array()) {

    $this->setControler($controler);

    $this->instance = $instance;
    $this->setArguments($aArguments);
  }

  protected function setArguments(array $aArguments) {

    $window = $this->getControler();

    foreach ($aArguments as $sKey => $mValue) {

      $this->aArguments[$sKey] = $window->argToInstance($mValue);
    }
  }

  public function getInterface() {

    return $this->instance->getInterface();
  }

  protected function getArguments() {

    return $this->aArguments;
  }

  public function addContent($mVar) {

    return $this->getControler()->add($mVar);
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'instanciate' => array(
        '@class' => $this->getInterface()->getName(),
        '#argument' => $this->getArguments(),
    )));
  }
}