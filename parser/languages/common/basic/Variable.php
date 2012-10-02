<?php

namespace sylma\parser\languages\common\basic;
use \sylma\core, \sylma\parser\languages\common;

class Variable extends Returned implements common\_var {

  private $sName = '';
  protected $ghost;

  public function __construct(common\_window $window, $sName, common\ghost $return = null) {

    $this->setName($sName);
    $this->setControler($window);

    if ($return) $this->setReturn($return);
  }

  protected function setName($sName) {

    $this->sName = $sName;
  }

  public function getName() {

    return $this->sName;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'variable' => array(
        '@name' => $this->sName,
      ),
    ));
  }
}