<?php

class Redirect {
  
  private $sPath = '/'; // URL cible
  private $oPath = null; // URL cible
  private $oSource = null; // URL de provenance
  private $sWindowType = 'html';
  private $bIsReal = false; // Défini si le cookie a été redirigé ou non
  
  private $aArguments = array();
  private $aDocuments = array();
  private $oMessages;
  
  public function __construct($sPath = '', $mMessages = array(), $aArguments = array()) {
    
    $this->setMessages($mMessages);
    
    if ($sPath) $this->setPath($sPath);
    
    $this->aArguments = $aArguments;
    //$this->setArgument('post', $_POST);
    //$this->setWindowType(Controler::getWindowType());
  }
  
  public function getArgument($sKey) {
    
    return (array_key_exists($sKey, $this->aArguments)) ? $this->aArguments[$sKey] : null;
  }
  
  public function setArgumentKey($sArgument, $sKey, $mValue = '') {
    
    $mArgument = $this->getArgument($sArgument);
    
    if (is_array($mArgument)) {
      
      if ($mValue) {
        
        $mArgument[$sKey] = $mValue;
        $this->setArgument($sArgument, $mArgument);
        
      } else unset($this->aArguments[$sArgument][$sKey]);
    }
  }
  
  public function setArgument($sKey, $mValue) {
    
    $this->aArguments[$sKey] = $mValue;
  }
  
  public function getArguments() {
    
    return $this->aArguments;
  }
  
  public function getDocuments() {
    
    return $this->aDocuments;
  }
  
  public function getDocument($sKey) {
    
    return (array_key_exists($sKey, $this->aDocuments)) ? $this->aDocuments[$sKey] : null;
  }
  
  public function setDocument($sKey, $oDocument) {
    
    $this->aDocuments[$sKey] = $oDocument;
  }
  
  public function setMessages($mMessages = array()) {
    
    $this->oMessages = new Messages(new XML_Document(Controler::getSettings('messages/allowed/@path')), $mMessages);
  }
  
  public function getMessages($sStatut = null) {
    
    if ($sStatut) return $this->oMessages->getMessages($sStatut);
    else return $this->oMessages;
  }
  
  public function addMessage($sMessage = '- message vide -', $sStatut = 'notice', $aArguments = array()) {
    
    $this->oMessages->addStringMessage($sMessage, $sStatut, $aArguments);
  }
  
  public function getPath() {
    
    return $this->oPath;
  }
  
  public function setPath($oPath) {
    
    $this->oPath = $oPath;
    return $oPath;
    // if ($sPath == '/' || $sPath != Controler::getPath()) $this->sPath = $sPath;
    // else Controler::errorRedirect(t('Un problème de redirection à été détecté !'));
  }
  
  public function getSource() {
    
    return $this->oSource;
  }
  
  public function setSource($oSource) {
    
    $this->oSource = $oSource;
    return $oSource;
  }
  
  public function isSource($sSource) {
    
    return ((string) $this->oSource == $sSource);
  }
  
  public function getWindowType() {
    
    return $this->sWindowType;
  }
  
  public function setWindowType($sWindowType) {
    
    $this->sWindowType = $sWindowType;
  }
  
  public function setReal($bValue = 'true') {
    
    $this->bIsReal = (bool) $bValue;
  }
  
  public function isReal() {
    
    return $this->bIsReal;
  }
  
  public function __sleep() {
    
    foreach ($this->aDocuments as $sKey => $oDocument) $this->aDocuments[$sKey] = (string) $oDocument;
    return array_keys(get_object_vars($this)); // TODO Ref or not ?
  }
  
  public function __wakeup() {
    
    foreach ($this->aDocuments as $sKey => $sDocument) $this->aDocuments[$sKey] = new XML_Document($sDocument);
  }
  
  public function __toString() {
    
    return (string) $this->oPath;
  }
}

