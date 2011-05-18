<?php

include('ReflectorInterface.php');
include('Reflector.php');
include('Class.php');
include('Property.php');
include('Method.php');
include('Parameter.php');

class Inspector extends Module {
  
  const MESSAGES_STATUT = 'warning';
  
  public function __construct() {
    
    $this->setArguments(Sylma::get('modules/inspector'));
    
    $this->setDirectory(__file__);
    $this->setNamespace('http://www.sylma.org/modules/inspector');
  }
  
  public function inspect() {
    
    // dspf($this->getFile('/sylma/system/sylma.yml')->getYAML());
    
    $action = $this->create('class', array(
      new ReflectionClass('InspectorClass'), $this));
    
    // dspf($this->
    // dspf($action->parse());
    // dspf($action->getSourceProperties());
    // return new HTML_Tag('pre', (string) $action);
  }
  
  public function log($sMessage, $sStatut = self::MESSAGES_STATUT) {
    
    parent::log($sMessage, $sStatut);
  }
}


