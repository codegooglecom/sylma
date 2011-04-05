<?php

require_once('Extension.php');

class XDB_Module extends ModuleExtension {
  
  /* Global */
  
  protected function getDB() {
    
    return Controler::getDatabase();
  }
  
  protected function setDocument($sDocument) {
    
    $this->sDocument = $sDocument;
  }
  
  protected function getCollection($bFormat = true) {
    
    return $this->getDB()->getCollection($bFormat);
  }
  
  protected function getPathID($sID, $sPath = null) {
    
    if ($sPath) {
      
      if ($sPath === true) $sPath = '';
      return $this->getPath($this->getPathID($sID).$sPath);
      
    } else return "//id('$sID')";
  }
  
  protected function getPath($sPath = '', $sDocument = null) {
    
    if (!$sDocument) $sDocument = $this->sDocument;
    
    return $this->getDB()->getPath($sDocument).$sPath;
  }
  
  /* Various */
  
  public function getCurrentDate() {
    
    return date('Y-m-d\TH:i:sP'); // 2011-02-11T16:20:53.921+01:00
  }
  
  /* XQuery */
  
  public function load($sID) {
    
   return $this->getDB()->load($sID);
  }
  
  public function get($sQuery, $bDocument = false, array $aNamespaces = array()) {
    
    return $this->getDB()->get($sQuery, $this->mergeNamespaces($aNamespaces), $bDocument, true, false);
  }
  
  protected function read($sQuery, array $aNamespaces = array()) {
    
    return $this->getDB()->query($sQuery, $this->mergeNamespaces($aNamespaces), true, false);
  }
  
  protected function query($sQuery, array $aNamespaces = array(), $bGetResult = true, $bGetMessages = true) {
    
    return $this->getDB()->query($sQuery, $this->mergeNamespaces($aNamespaces), $bGetResult, $bGetMessages);
  }
  
  protected function update($sQuery, array $aNamespaces = array()) {
    
    return $this->getDB()->query($sQuery, $this->mergeNamespaces($aNamespaces), false, false);
  }
  
  public function check($sPath, $aNamespaces = array()) {
    
   return $this->getDB()->check($sPath, $this->mergeNamespaces($aNamespaces));
  }
  
  public function checkID($sID) {
    
    return $this->getDB()->check($this->getPathID($sID));
  }
  
  protected function replace($sPath, XML_Document $oDocument, array $aNamespaces = array()) {
    
    return $this->getDB()->replace($sPath, $oDocument, $this->mergeNamespaces($aNamespaces));
  }
  
  protected function replaceID($sID, XML_Document $oDocument, array $aNamespaces = array()) {
    
    return $this->replace($this->getPath($this->getPathID($sID)), $oDocument, $aNamespaces);
  }
  
  protected function insert(XML_Document $mElement, $sTarget, array $aNamespaces = array()) {
    
    return $this->getDB()->insert($mElement, $sTarget, $this->mergeNamespaces($aNamespaces));
  }
  
  protected function delete($sID, array $aNamespaces = array()) {
    
    return $this->getDB()->delete($sID, $this->mergeNamespaces($aNamespaces));
  }
}

