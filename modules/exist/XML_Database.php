<?php

include('eXist.php');

class XML_Database {
  
  private $oSession;
  
  private $aNamespaces = array();
  private $sNamespace = '';
  private $sCollection = '';
  private $iHits = 0; // temp value for result's hits count
  
  public function __construct() {
    
    try {
      
      $db = new eXist(Sylma::get('db/user'), Sylma::get('db/password'), Sylma::get('db/host'));
      if (!$db->connect()) dspm($db->getError(), 'db/error');
      
      //$this->sDatabase = $sDatabase;
      $this->oSession = $db;
      $this->sNamespace = Sylma::get('db/namespace');
      $this->sCollection = Sylma::get('db/collection');
      
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
      
      if (Sylma::get('db/debug/show-queries'))
        dspm(array(t('xquery [query] '), $oResults, new HTML_Tag('pre', $sQuery)), 'db/notice');
      if (Sylma::get('db/debug/show-queries'))
        dspm(array(t('xquery [result] '), $oResults, new HTML_Tag('pre', $sResult)), 'db/notice');
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