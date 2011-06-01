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
  
  public function getDeclared() {
    
    try {
      
      $system = new XArguments((string) $this->getFile('system-classes.yml'));
      $aAll = get_declared_classes();
      
      $root = $this->create('element', array('classes', null, null, self::NS));
      foreach (array_diff($aAll, $system->query()) as $sClass) $root->addNode('class', $sClass);
      
      return $root->getDocument();
      
    } catch (SylmaException $e) {
      
      return null;
    }
  }
  
  public function getClass($sClass) {
    
    try {
      
      $class = $this->create('class', array($sClass, $this));
      
      //dspf($class->parse());
      return $class->parse();
      
    } catch (SylmaException $e) {
    	
      return null;
    }
  }
}


