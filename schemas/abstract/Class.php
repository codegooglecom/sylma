<?php

require_once('Basic.php');

abstract class XSD_Class extends XSD_Basic { // Used by XSD_Particle, XSD_GroupReference
  
  protected $aInstances = array(); // instanced particles derived from this particle
  
  abstract public function getInstance($oParent);
  //abstract protected function buildInstance(XSD_Instance $oParent);
  
  public function isRequired() {
    
    return intval($this->getMin()) > 1;
  }
  
  public function getInstances() {
    
    return $this->aInstances;
  }
  
  protected function hasInstance(XSD_Instance $oNeedle) {
    
    foreach ($this->aInstances as $oInstance) if ($oNeedle === $oInstance) return true;
    
    return false;
  }
}

