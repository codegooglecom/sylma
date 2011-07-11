<?php

require_once('Timer.php');

class TimerArguments extends ModuleBase {
  
  protected $args;
  
  public function __construct() {
    
    $this->arguments = new Arguments;
  }
  
  public function open($sMethod) {
    
    list($sClass, $sMethod) = explode('::', $sMethod);
    $sPath = $sClass . '/' . $sMethod;
    
    if (!$method = $this->getArgument($sPath, null, false)) {
      
      $method = $this->getArguments()->set($sPath, array(
        '@name' => $sMethod,
        'calls' => 0,
        'time' => 0));
    }
    
    $method->set('calls', $method->get('calls') + 1);
    
    $this->aStack[] = array($method, microtime(true));
  }
  
  public function close() {
    
    if (!count($this->aStack)) return;
    list($method, $iCurrent) = array_pop($this->aStack);
    
    $method->set('time', $method->get('time') + (microtime(true) - $iCurrent));
  }
  
  public function parse() {
    
    $aResult = array();
    
    foreach ($this->getArguments() as $sClass => $class) {
      
      $aResult['#class'][] = array('@name' => $sClass, '#method' => array_values($class->query()));
    }
    
    $result = Arguments::buildDocument(array('classes' => $aResult), Timer::NS);
    
    return $result;
  }
}


