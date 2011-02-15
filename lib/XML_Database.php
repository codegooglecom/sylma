<?php

class XML_Database {
  
  private $oSession;
  
  private $aNamespaces = array();
  private $sNamespace = '';
  private $sCollection = '';
  private $iHits = 0; // temp value for result's hits count
  
  public function __construct($aDB) {
    
    try {
      
      $db = new eXist($aDB['user'], $aDB['password'], $aDB['host']);
      if (!$db->connect()) dspm($db->getError(), 'db/error');
      
      //$this->sDatabase = $sDatabase;
      $this->oSession = $db;
      $this->sNamespace = $aDB['namespace'];
      $this->sCollection = $aDB['collection'];
      
    } catch (Exception $e) {
      
      dspm(xt('Impossible de se connecter au serveur de base de donnée : %s', $e->getMessage()), 'db/error');
    }
  }
  
  public function getError() {
    
    return $this->getSession() ? $this->getSession()->getError() : null;
  }
  
  public function getSession() {
    
    return $this->oSession;
  }
  
  public function getNamespace($sPrefix = '') {
    
    if ($sPrefix) return $this->aNamespaces[$sPrefix];
    else return $this->sNamespace;
  }
  
  public function setNamespaces($aNamespace) {
    
    foreach ($aNamespaces as $sPrefix => $sNamespace) $this->setNamespace($sPrefix, $sNamespace);
  }
  
  public function setNamespace($mNamespace, $sPrefix = '') {
    
    if ($sPrefix) $this->aNamespaces[$sPrefix] = $mNamespace;
    else $this->sNamespace = $mNamespace;
  }
  
  public function query($sQuery, array $aNamespaces = array(), $bGetResult = true, $bMessages = true) {
    
    $sResult = null;
    
    if (!$this->getSession()) dspm(t('Aucun base de données instanciées'), 'db/error');
    else {
      
      $sDeclare = ''; // namespaces declarations
      $aNamespaces = array_merge(array($this->sNamespace), $this->aNamespaces, $aNamespaces);
      
      foreach ($aNamespaces as $sPrefix => $sNamespace) {
        
        if ($sPrefix === 0) $sDeclare .= "declare default element namespace '{$sNamespace}';\n";
        else if ($sPrefix) $sDeclare .= "declare namespace {$sPrefix}='{$sNamespace}';\n";
      }
      
      $sQuery = $sDeclare.$sQuery;
      
      $hits = 0;
      $queryTime = 0;
      
      if (!$aResult = $this->getSession()->xquery($sQuery)) { // no result
        
        if ($bGetResult && $bMessages) {
          
          dspm(array(
            new HTML_Strong(t('Erreur dans la requête : ')),
            $this->getError(),
            new HTML_Hr,
            new HTML_Tag('pre', $sQuery)), 'db/warning');
            
        } else if (($sError = $this->getSession()->getError()) && $sError != 'ERROR: No data found!') {
          
          dspm($sError, 'db/error');
          
        } else $sResult = '';
        
      } else { // has result
        
        $sResult = '';
        
        $hits = $aResult['HITS'];
        $queryTime = $aResult['QUERY_TIME'];
        // $collections = $aResult['COLLECTIONS'];
        
        if (!empty($aResult['XML'])) {
          
          if (is_string($aResult['XML'])) $sResult = $aResult['XML'];
          else {
            
            foreach ($aResult['XML'] as $sItem) $sResult .= $sItem;
          }
        }
      }
      
      $oResults = xt('[ time : %s s] [ hits : %s ]',
        new HTML_Strong(floatval($queryTime / 1000)),
        new HTML_Strong($hits));
      
      if (SYLMA_DB_SHOW_QUERIES) dspm(array(t('xquery [query] '), $oResults, new HTML_Tag('pre', $sQuery)), 'db/notice');
      if (SYLMA_DB_SHOW_RESULTS) dspm(array(t('xquery [result] '), $oResults, new HTML_Tag('pre', $sResult)), 'db/notice');
    }
    
    return $sResult;
  }
  
  public function get($sQuery, array $aNamespaces = array(), $bDocument = false, $bGetResult = true, $bMessages = true) {
    
    $mResult = false;
    
    if ($sResult = $this->query($sQuery, $aNamespaces, $bGetResult, $bMessages)) {
      
      $mResult = strtoxml($sResult);
      if ($bDocument) $mResult = new XML_Document($mResult);
    }
    
    return $mResult;
  }
  
  public function escape($sValue) {
    
    return addQuote($sValue);
  }
  
  public function addDocument($sDocument, XML_Document $oRoot) {
	
	if (!$this->check($this->getPath($sDocument))) $this->query("xmldb:store('{$this->getCollection(false)}', '{$sDocument}', {$oRoot->display(true, false)})", array(), false, false);
	//$this->update("xmldb:chmod-resource('{$this->getCollection(false)}', '{$this->sDocument}', $mode as xs:integer)util:base-to-integer(0755, 8)");
  }
  
  public function hasDocument($sDocument) {
    
    return 'xs:boolean('.$this->getPath($sDocument).')';
  }
  
  public function getPath($sDocument) {
    
    if (!$sDocument) dspm('Aucun nom pour le document', 'db/warning');
    else if ($sDocument[0] != '/') $sDocument = '/'.$sDocument;
    
    return "doc('{$this->getCollection()}$sDocument')";
  }
  
  public function getCollection($bFormat = false) {
    
    if ($bFormat) return "collection('{$this->sCollection}')";
    else return $this->sCollection;
  }
  
  public function check($sPath, array $aNamespaces = array()) {
    
    return $this->query("if ($sPath) then 1 else ''", $aNamespaces, true, false);
  }
  
  public function load($sID) {
    
    if ($sResult = $this->query("{$this->getCollection(true)}//id('$sID')")) return new XML_Document($sResult);
    return null;
  }
  
  public function delete($sID, array $aNamespaces = array()) {
    
    return $this->query("update delete {$this->getCollection(true)}//id('$sID')", $aNamespaces, false);
  }
  
  public function replace($sPath, XML_Document $oDocument, array $aNamespaces = array()) {
    
    return $this->query("update replace $sPath with {$oDocument->display(true, false)}", $aNamespaces, false, false);
  }
  
  public function insert(XML_Document $oDocument, $sPath, array $aNamespaces = array()) {
    
    return $this->query("update insert {$oDocument->display(true, false)} into $sPath", $aNamespaces, false);
  }
  
  public function __destruct() {
    
    if ($this->oSession && !$this->getSession()->disconnect()) {
      //dspm(xt('Erreur pendant la déconnexion : %s', $this->getError()), 'db/error');
    }
  }
}

class XDB_Module extends Module {
  
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
  
  public function mergeNamespaces($aNamespaces = array()) {
    
    if ($aNamespaces) return array_merge($this->getNS(), $aNamespaces);
    else return $this->getNS();
  }
  
  public function get($sQuery, $bDocument = false, array $aNamespaces = array()) {
    
    return $this->getDB()->get($sQuery, $this->mergeNamespaces($aNamespaces), $bDocument, true, false);
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




