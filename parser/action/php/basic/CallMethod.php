<?php

namespace sylma\parser\action\php\basic;
use \sylma\core, \sylma\parser\action\php;

require_once('Controled.php');
require_once('core/argumentable.php');

class CallMethod extends Controled implements core\argumentable  {
  
  private $sMethod;
  private $called;
  protected $return;
  
  var $aArguments;
  
  public function __construct(Window $controler, php\_object $called, $sMethod, php\_instance $return, array $aArguments = array()) {
    
    $this->called = $called;
    $this->sMethod = $sMethod;
    $this->setControler($controler);
    $this->setReturn($return);
    
    $this->aArguments = $this->parseArguments($aArguments);
  }
  
  protected function getReturn() {
    
    return $this->return;
  }
  
  protected function setReturn(php\_instance $return) {
    
    $this->return = $return;
  }
  
  protected function parseArguments($aArguments) {
    
    $window = $this->getControler();
    $aResult = array();
    
    foreach ($aArguments as $mVar) {
      
      $aResult[] = $window->parseArgument($mVar);
    }
    
    return $aResult;
  }
  
  public function getVar() {
    
    $window = $this->getControler();
    
    $var = $this->getReturn();
    
    if ($var instanceof ObjectInstance) $sAlias = 'object-var';
    else $sAlias = 'simple-var';
    
    $var = $window->create($sAlias, array($var, $window->getVarName()));
    $assign = $window->create('assign', array($window, $var, $this));
    
    $window->addScope($assign);
    
    return $var;
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