<?php

namespace sylma\parser\action\php;
use \sylma\core;

require_once('Controled.php');
require_once('core/argumentable.php');

class CallMethod extends Controled implements core\argumentable  {
  
  private $sMethod;
  private $called;
  var $aArguments;
  
  public function __construct(Window $controler, ObjectInterface $called, $sMethod, array $aArguments = array()) {
    
    $this->called = $called;
    $this->sMethod = $sMethod;
    $this->setControler($controler);
    
    $this->aArguments = $this->parseArguments($aArguments);
  }
  
  public function parseArguments($aArguments) {
    
    $window = $this->getControler();
    $aResult = array();
    
    foreach ($aArguments as $mVar) {
      
      $aResult[] = $window->parseArgument($mVar);
    }
    
    return $aResult;
  }
  
  public function getResult() {
    
    
  }
  
  public function asArgument() {
    
    return $this->getControler()->createArgument(array(
      'call' => array(
          '@name' => $this->sMethod,
          'called' => $this->called,
          '#argument' => $this->aArguments,
      ),
    ));
  }
}