<?php

include('ReflectorInterface.php');
include('Reflector.php');
include('Class.php');
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
  
  public function inspect() {
    
    $sResult = '';
    
    try {
      
      $action = $this->create('class', array(
        new ReflectionClass('InspectorClass'), $this));
      
      dspf($action->parse());
      $sResult = (string) $action;
      
    } catch (Exception $e) {
      
      Sylma::sendException($e);
    }
    
    return new HTML_Tag('pre', $sResult);
  }
  
  public function log($mPath, $sMessage, $sStatut = self::MESSAGES_STATUT) {
    
    parent::log($mPath, $sMessage, $sStatut);
  }
}


