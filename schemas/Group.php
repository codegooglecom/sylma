<?php

require_once('abstract/Container.php');
require_once('GroupInstance.php');
require_once('GroupReference.php');

class XSD_Group extends XSD_Container {
  
  public function __construct(XML_Element $oSource, $oParent, $oNode = null, XSD_Parser $oParser = null) {
    
    parent::__construct($oSource, $oParent, $oNode, $oParser);
    
    $this->sPath = $oSource->getAttribute('name');
    $this->build();
  }
  
  private function validate($oInstance) {
    
    $this->getParticle()->validate($oInstance->getParticle());
  }
  
  private function getInstance(XSD_Instance $oParent) {
    
    // TODO occurs
    $oElement = new XML_Element('group', null, array('ref' => $this->getName()), $this->getNamespace());
    $oGroupRef = new XSD_GroupReference($oElement, $oParent);
    
    return $oGroupRef->getInstance($oParent); 
  }
  
  private function build() {
    
    if (!$oFirst = $this->getSource()->getFirst()) {
      
      $this->dspm(xt('Impossible de construire le groupe %s, car il ne possède aucun enfant', view($this->getSource())), 'xml/error');
      
    } else {
      
      $this->oParticle = new XSD_Particle($oFirst, $this);
    }
  }
  
  public function getPath() {
    
    return $this->getName();
  }
  
  public function getElement(XML_Element $oElement) {
    
    return $this->getParticle()->getElement($oElement);
  }
  
  public function parse() {
    
    return new XML_Element('group', $this->getParticle(), array('name' => $this->getName()), $this->getNamespace());
  }
}

