<?php

class XDB_Module extends ModuleExtension {
  
  /* Global */
  
  protected function getDB() {
    
    return Controler::getDatabase();
  }
  
  protected function setDocument($sDocument, XML_Document $oContent = null) {
    
    if ($sDocument) {
      
      if (Controler::isAdmin() && $this->getDB()->getArgument('install')) $this->addDocument($sDocument, $oContent);
      $this->sDocument = $sDocument;
    }
  }
  
  protected function addDocument($sPath, XML_Document $oContent = null) {
    
    $aPath = explode('/', $sPath);
    
    if (count($aPath) > 1) {
      
      $sName = array_pop($aPath);
      if (!$sCollection = $this->addCollection(implode('/', $aPath))) return null;
    }
    else {
      
      $sName = $sPath;
      $sCollection = '';
    }
    
    return $this->getDB()->createDocument($sName, $sCollection, $oContent, false);
  }
  
  protected function addCollection($sPath) {
    
    $aPath = explode('/', $sPath);
    $sBase = '';
    
    if (!$this->check($this->getDB()->callCollection($sPath))) {
      
      foreach ($aPath as $sCollection) {
        
        if (!$sCollection) {
        
          $this->dspm(xt('Chemin invalide dans %s, impossible de créer la collection %s',
            new HTML_Strong($sPath),
            view($sCollection)), 'action/warning');
          
          break;
        }
        else {
          
          $mPath = $this->getDB()->createCollection($sCollection, $sBase);
          if ($mPath === null) break;
        }
        
        $sBase .= '/' . $sCollection;
      }
    }
    
    return $sPath;
  }
  
  protected function getCollection($bFormat = true) {
    
    return $this->getDB()->getCollection($bFormat);
  }
  
  /**
   * Get a string of type :
   * - //id('12') - @param $sID = 12 and (bool) @param $sPath = FALSE
   * - doc('current')//id('12') - @param $sPath = TRUE
   * - doc('current')//id('12')/blabla - @param $sPath = '/blabla'
   */
  protected function getPathID($sID, $sPath = null) {
    
    if ($sPath) {
      
      if ($sPath === true) $sPath = '';
      return $this->getPath($this->getPathID($sID).$sPath);
      
    } else return "//id('$sID')";
  }
  
  protected function getPath($sPath = '', $sDocument = null) {
    
    if (!$sDocument) $sDocument = $this->sDocument;
    
    return $this->getDB()->callDocument($sDocument).$sPath;
  }
  
  /* Various */
  
  public function createNode($sName, $mContent = '', array $aAttributes = array(), $sPrefix = '') {
    
    return new XML_Element($sName, $mContent, $aAttributes, $this->getNamespace($sPrefix));
  }
  
  public function getCurrentDate() {
    
    return date('Y-m-d\TH:i:sP'); // 2011-02-11T16:20:53.921+01:00
  }
  
  /**
   * Alias of @method XML_Database->escape()
   */
  
  public function escape() {
    
    $this->getDB()->escape(func_get_args());
  }
  
  /* XQuery */
  
  public function load($sID) {
    
   return $this->getDB()->load($sID);
  }
  
  // get result as element/document, no [empty result] messages
  public function get($sQuery, $bDocument = false, array $aNamespaces = array()) {
    
    return $this->getDB()->get($sQuery, $this->mergeNamespaces($aNamespaces), $bDocument, true, false);
  }
  
  // get result as string, no [empty result] messages
  protected function read($sQuery, array $aNamespaces = array()) {
    
    return $this->getDB()->query($sQuery, $this->mergeNamespaces($aNamespaces), true, false);
  }
  
  // custom
  protected function query($sQuery, array $aNamespaces = array(), $bGetResult = true, $bGetMessages = true) {
    
    return $this->getDB()->query($sQuery, $this->mergeNamespaces($aNamespaces), $bGetResult, $bGetMessages);
  }
  
  // no result, no [empty result] messages
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

