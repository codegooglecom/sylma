<?php

namespace sylma\parser\languages\php\basic;
use \sylma\core, \sylma\parser\languages\common, \sylma\parser\languages\php;

abstract class Called extends common\basic\Controled implements common\argumentable, common\_call  {

  protected $sName;

  protected $return;

  protected $aArguments;

  public function getName() {

    return $this->sName;
  }

  public function setName($sName) {

    $this->sName = $sName;
  }

  /**
   * @return array
   */
  public function getArguments() {

    return $this->aArguments;
  }

  public function setArguments(array $aArguments) {

    $this->aArguments = $aArguments;
  }

  public function getReturn() {

    return $this->return;
  }

  protected function setReturn(common\_instance $return) {

    $this->return = $return;
  }

  protected function parseArguments($aArguments) {

    $window = $this->getControler();
    $aResult = array();

    foreach ($aArguments as $mVar) {

      $arg = $window->argToInstance($mVar);

      if ($arg instanceof php\basic\instance\_Object) {

        $this->getControler()->throwException('Cannot add object instance here');
      }

      $aResult[] = $arg;
    }

    return $aResult;
  }
}