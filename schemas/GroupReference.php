<?php

require_once('abstract\Class.php');

class XSD_GroupReference extends XSD_Class {
  
  private $oGroup = null;
  
  public function __construct(XML_Element $oSource, $oParent) {
    
    parent::__construct($oSource, $oParent, $oNode, $oParser);
    
    $this->oGroup = $this->getParser()->getGroup($oSource, $this);
  }
  
  public function getParticle() {
    
    return $this->getGroup()->getParticle();
  }
  
  public function validate($oInstance) {
    
    // TODO occurences
    
    if ($oInstance && $this->hasInstance($oInstance)) {
      
      $bResult = $this->getGroup()->validate($oInstance);
      
    } else {
      
      $bResult = false;
      
      if ($this->useMessages()) $oInstance->addMessage(
        xt('Le groupe %s est manquant dans %s',
        new HTML_Strong($this->getClass()->getName()), view($this->getName())), 'content', 'invalid');
      
      $oInstance->isValid(false);
      
      if ($this->keepValidate()) $this->buildInstance($oInstance->getParent()); // DEBUG
    }
    
    return $bResult;
  }
  
  protected function buildInstance(XSD_Instance $oParent) {
    
    $oInstance = $this->getInstance($oParent);
    $oInstance->validate();
    
    $oParent->insert($oInstance);
  }
  
  public function getGroup() {
    
    return $this->oGroup;
  }
  
  public function getName() {
    
    return $this->getGroup()->getName();
  }
  
  public function getElement(XML_Element $oElement) {
    
    return $this->getGroup()->getElement($oElement);
  }
  
  public function getInstance($oParent) {
    
    return new XSD_GroupInstance($this, $oParent);
  }
  
  public function parse() {
    
    return new XML_Element('group', null, array('ref' => $this->getName()), $this->getNamespace());
  }
}

