<?php

require_once('abstract/Instance.php');

class XSD_GroupInstance extends XSD_Instance {
  
  private $oParticle = null;
  
  public function __construct($oClass, $oParent) {
    
    parent::__construct($oClass, $oParent);
    
    $this->oParticle = new XSD_ParticleInstance($oClass->getParticle(), $this);
  }
  
  private function getParticle() {
    
    return $this->oParticle;
  }
  
  public function add(XML_Element $oElement, array $aParents) {
    
    $this->getParticle()->add($oElement, $aParents);
  }
  
  public function parse() {
    
    $oGroup = new XML_Element('group', $this->getParticle(), array('name' => $this->getClass()->getName()), $this->getNamespace());
    
    return $oGroup;
  }
}

