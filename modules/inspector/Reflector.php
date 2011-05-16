<?php

abstract class InspectorReflector {
  
  const MESSAGES_STATUT = 'warning';
  
  protected $reflector;
  protected $controler;
  
  protected function getReflector() {
    
    return $this->reflector;
  }
  
  protected function getControler() {
    
    return $this->controler;
  }
  
  public function sendError($sMessage, $sStatut = self::MESSAGES_STATUT) {
    
    $this->getControler()->dspm($sMessage, $sStatut);
  }
}
