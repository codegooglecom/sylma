<?php

namespace sylma\parser\action\php\basic;
use \sylma\core, \sylma\parser\action\php;

require_once('Called.php');
require_once('core/argumentable.php');

class CallMethod extends Called  {
  
  private $called;
  
  public function __construct(Window $controler, php\_object $called, $sMethod, php\_instance $return, array $aArguments = array()) {
    
    $this->called = $called;
    $this->setName($sMethod);
    $this->setControler($controler);
    $this->setReturn($return);
    
    $this->setArguments($this->parseArguments($aArguments));
  }
  
  public function asArgument() {
    
    return $this->getControler()->createArgument(array(
      'call' => array(
          '@name' => $this->getName(),
          'called' => $this->called,
          '#argument' => $this->getArguments(),
      ),
    ));
  }
}