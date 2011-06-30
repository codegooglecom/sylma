<?php

abstract class XSD_Instance { // Used by XSD_Model, XSD_ParticleInstance, XSD_GroupInstance
  
  private $oParent = null;
  private $oClass = null;
  
  private $aMessages = array();
  private $sStatut = '';
  
  private $bValid = true;
  
  public function __construct(XSD_Basic $oClass, XSD_Instance $oParent = null) {
    
    $this->oClass = $oClass;
    $this->oParent = $oParent;
  }
  
  protected function getMessages() {
  
    return $this->aMessages;
  }
  
  public function addMessage($mMessage, $sContext, $sStatut = 'invalid') {
    
    $oMessage = null;
    
    if ($this->useMessages()) {
      
      $oMessage = new XML_Element('message', $mMessage,
        array('context' => $sContext, 'statut' => $sStatut), $this->getNamespace());
      
      $this->getParser()->addMessage($this, $oMessage);
      $this->aMessages[] = $oMessage;
    }
    
    //return $oMessage;
  }
  
  public function addMessages($aMessages) {
    
    $this->aMessages += $aMessages;
  }
  
  public function useMessages() { // TODO : replicate of XSD_Basic
    
    return $this->getParser()->useMessages();
  }
  
  public function isValid($bValid = null) {
    
    if (!$bValid && $bValid !== null) {
      
      $this->getParser()->isValid(false);
      $this->bValid = false;
    }
    
    return $this->bValid;
  }
  
  public function keepValidate() { // TODO : replicate of XSD_Basic
    
    return $this->getParser()->keepValidate();
  }
  
  public function getStatut() {
    
    return $this->sStatut;
  }
  
  public function setStatut($sStatut) {
    
    $this->sStatut = $sStatut;
  }
  
  public function getParent() {
    
    return $this->oParent;
  }
  
  public function getParser() {
    
    return $this->getClass()->getParser();
  }
  
  public function getModel() {
    
    return $this->getParent()->getModel();
  }
  
  public function getName() {
    
    return $this->getClass()->getName();
  }
  
  public function getNode() {
    
    return $this->getModel()->getNode();
  }
  
  public function getNamespace() {
    
    return $this->getClass()->getNamespace();
  }
  
  public function getClass() {
    
    return $this->oClass;
  }
}

