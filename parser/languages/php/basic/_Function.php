<?php

namespace sylma\parser\languages\php\basic;
use \sylma\core, \sylma\parser\languages\common, \sylma\parser\languages\php;

class _Function extends Called {

  public function __construct(common\_window $controler, $sName, common\_instance $return, array $aArguments = array()) {

    $this->setControler($controler);

    $this->setName($sName);
    $this->setArguments($this->parseArguments($aArguments));

    $this->setReturn($return);
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
       'function' => array(
           '@name' => $this->getName(),
           '#argument' => $this->getArguments(),
       )
    ));
  }
}