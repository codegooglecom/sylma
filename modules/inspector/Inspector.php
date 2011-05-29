<?php

include('ReflectorInterface.php');
include('Reflector.php');
include('Class.php');
include('Constant.php');
include('Property.php');
include('Method.php');
include('Parameter.php');

class Inspector extends Module {
  
  const MESSAGES_STATUT = 'warning';
  const NS = 'http://www.sylma.org/modules/inspector';
  
  public function __construct() {
    
    $this->setArguments(Sylma::get('modules/inspector'));
    
    $this->setDirectory(__file__);
    $this->setNamespace(self::NS);
  }
  
  public function getClass($sClass) {
    
    try {
      
      $action = $this->create('class', array(
        new ReflectionClass($sClass), $this));
      
      //dspf($action->parse());
      //$sResult = (string) $action;
      dspf($action->parse());
      return $action->parse();
      
    } catch (SylmaException $e) {
    	
      return null;
    }
  }
}


