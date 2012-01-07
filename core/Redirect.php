<?php

namespace sylma\core;

class Redirect {
  
  private $oPath = null; // URL cible
  private $oSource = null; // URL de provenance
  private $sExtension = 'html';
  
  
  public function __construct($sPath = '') {
    
    if ($sPath) $this->setPath($sPath);
    
    //$this->setWindowType(Controler::getWindowType());
  }
  
  public function getDocument($sKey) {
    
    return (array_key_exists($sKey, $this->aDocuments)) ? $this->aDocuments[$sKey] : null;
  }
  
  public function setDocument($sKey, $oDocument) {
    
    $this->aDocuments[$sKey] = $oDocument;
  }
  
  public function getPath() {
    
    return $this->oPath;
  }
  
  public function setPath($oPath) {
    
    $this->oPath = $oPath;
  }
  
  public function getSource() {
    
    return $this->oSource;
  }
  
  public function setSource($oSource) {
    
    $this->oSource = $oSource;
  }
  
  public function isSource($sSource) {
    
    return ((string) $this->oSource == $sSource);
  }
  
  public function getExtension() {
    
    return $this->sExtension;
  }
  
  public function setExtension($sExtension) {
    
    $this->sExtension = $sExtension;
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

