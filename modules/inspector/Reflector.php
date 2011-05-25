<?php

abstract class InspectorReflector {
  
  protected $reflector;
  protected $controler;
  
  protected function getReflector() {
    
    return $this->reflector;
  }
  
  protected function getControler() {
    
    return $this->controler;
  }
  
  protected function getName() {
    
    return $this->getReflector()->getName();
  }
  
  protected function throwException($sMessage) {
    
    $e = new Sylma::$exception($sMessage);
    
    $this->log($e->getPath(), $sMessage);
    throw $e;
  }
}
