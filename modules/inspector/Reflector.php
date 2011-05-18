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
  
  public function log($sMessage) {
    
    $this->getControler()->log($sMessage);
  }
}
