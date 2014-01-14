<?php

namespace sylma\parser\languages\php\basic;
use sylma\core, sylma\parser\languages\common;

class Instanciate extends common\basic\Instanciate implements common\_object, common\_instance, common\argumentable {

  protected function setArguments(array $aArguments) {

    $window = $this->getControler();

    foreach ($aArguments as $sKey => $mValue) {

      $this->aArguments[$sKey] = $window->argToInstance($mValue);
    }
  }

  public function getInterface() {

    return $this->instance->getInterface();
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'instanciate' => array(
        '@class' => $this->getInterface()->getName(),
        '#argument' => $this->getArguments(),
    )));
  }
}