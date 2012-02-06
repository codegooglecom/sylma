<?php

namespace sylma\parser\action\php\basic;
use \sylma\core, \sylma\parser\action\php;

require_once('core/argumentable.php');
require_once('Controled.php');

class _Function extends Called {

  public function __construct(php\_window $controler, $sName, php\_Instance $return, array $aArguments = array()) {

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