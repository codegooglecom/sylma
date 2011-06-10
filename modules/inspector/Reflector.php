<?php

require_once('ReflectorInterface.php');

abstract class InspectorReflector {
  
  protected $reflector;
  protected $parent;
  
  protected function getReflector() {
    
    return $this->reflector;
  }
  
  protected function getControler() {
    
    return $this->getParent()->getControler();
  }
  
  protected function getParent() {
    
    return $this->parent;
  }
  
  protected function getName() {
    
    return $this->getReflector()->getName();
  }
  
  protected function throwException($sMessage, $mSender = array()) {
  	
    $mSender = (array) $mSender;
    
    $e = new Sylma::$exception($sMessage);
    $e->setPath($mSender);
    $e->loadException();
    
    throw $e;
  }
}
