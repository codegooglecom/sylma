<?php

include('eXist.php');

class XML_Database extends ModuleBase {
  
  private $oSession;
  
  private $aNamespaces = array();
  private $sNamespace = '';
  private $sCollection = '';
  private $iHits = 0; // temp value for result's hits count
  
  public function __construct() {
    
    $this->setName('database');
    $this->setArguments(Sylma::get('db'));
    $this->connect();
  }
  
  public function connect() {
    
    try {
      
      $aUser = Controler::getUser()->getArgument('db');
      $sUser = $aUser ? $aUser['name'] : $this->getArgument('user');
      $sPassword = $aUser ? $aUser['password'] : $this->getArgument('password');
      
      $this->setArguments(Sylma::get('db'));
      
      $db = new eXist($sUser, $sPassword, $this->getArgument('host'));
      if (!$db->connect()) dspm($db->getError(), 'db/error');
      
      //$this->sDatabase = $sDatabase;
      $this->oSession = $db;
      $this->sNamespace = $this->getArgument('namespace');
      $this->sCollection = $this->getArgument('collection');
      
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
    $aResult = array();
    
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
      
      if ($this->getArgument('debug/run')) $aResult = $this->getSession()->xquery($sQuery);
      
      if ($this->getArgument('debug/run') && !$aResult) { // no result
        
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
        
        $hits = array_val('HITS', $aResult);
        $queryTime = array_val('QUERY_TIME', $aResult);
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
      
      if ($this->getArgument('debug/queries/show'))
        dspm(array(t('xquery [query] '), $oResults, new HTML_Tag('pre', $sQuery)), $this->getArgument('debug/queries/statut'));
      if ($this->getArgument('debug/results/show'))
        dspm(array(t('xquery [result] '), $oResults, new HTML_Tag('pre', $sResult)), $this->getArgument('debug/results/statut'));
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
  
  public function escape() {
    
    if (func_num_args() == 1) return addQuote(func_get_arg(0));
    else return addQuote(func_get_args());
  }
  
  public function createDocument($sName, $sRelativePath = '', XML_Document $oRoot = null, $bDebug = true) {
    
    $mResult = null;
    
    $sPath = $this->getAbsolutePath($sRelativePath);
    $sFullPath = $sPath . '/' . $sName; // full document name
    
    if ($this->check($this->callDocument($sFullPath))) {
      
      if ($bDebug) dspm(xt('The document %s is already existing', new HTML_Strong($sFullPath)), 'db/warning');
      $mResult = false;
    }
    else {
      
      if ($oRoot) $sContent = $oRoot->display(true, false);
      else $sContent = "''";
      
      list($sPath, $sName) = $this->escape($sPath, $sName);
      
      if ($this->query("xmldb:store($sPath, $sName, $sContent)")) {
        
        $sUser = $this->getArgument('user');
        $sGroup = $this->getArgument('default/group');
        $sMode = $this->getArgument('default/mode');
        
        list($sUser, $sGroup, $sMode) = $this->escape($sUser, $sGroup, $sMode);
        
        $this->update("xmldb:set-resource-permissions($sPath, $sName, $sUser, $sGroup, util:base-to-integer($sMode, 8))");
        
        $mResult = $sFullPath;
      }
    }
    
    return $mResult;
    
    
  }
  
  public function hasDocument($sDocument) {
    
    return 'xs:boolean('.$this->callDocument($sDocument).')';
  }
  
  public function createCollection($sName, $sRelativePath = '', $bDebug = true) {
    
    $mResult = null;
    
    $sPath = $this->getAbsolutePath($sRelativePath);
    $sFullPath = $sPath . '/' . $sName;
    
    if ($this->check($this->callCollection($sFullPath))) {
      
      if ($bDebug) dspm(xt('The collection %s is already existing', new HTML_Strong($sFullPath)), 'db/warning');
      $mResult = false;
    }
    else {
      
      list($sPath, $sName) = $this->escape($sPath, $sName);
      if (!$sPath) $sPath = "''";
      
      if ($this->query("xmldb:create-collection($sPath, $sName)")) { // if ok, return real path
        
        $sUser = $this->getArgument('user');
        $sGroup = $this->getArgument('default/group');
        $sMode = $this->getArgument('default/mode');
        
        list($sFullPath, $sUser, $sGroup, $sMode) = $this->escape($sFullPath, $sUser, $sGroup, $sMode);
        
        $this->update("xmldb:set-collection-permissions($sFullPath, $sUser, $sGroup, util:base-to-integer($sMode, 8))");
        
        $mResult = $sFullPath;
      }
    }
    
    return $mResult;
  }
  
  public function getAbsolutePath($sPath, $sBase = '') {
    
    $sResult = '';
    
    if ($sPath && $sPath[0] == '/') {
      
      $sResult = $sPath;
    }
    else {
      
      if ($sBase && $sBase[0] != '/') {
        
        dspm('Base path %s must be absolute or empty to guess relative path %s',
          new HTML_Strong($sBase),
          new HTML_Strong($sPath), 'db/warning');
      }
      else {
        
        $sResult = $this->getCollection() . $sBase . ($sPath ? '/'.$sPath : '');
      }
    }
    
    return $sResult;
  }
  
  public function callCollection($sCollection, $sBase = '') {
    
    $sResult = '';
    
    if (!$sCollection) dspm('Empty name is not allowed for collection creation', 'db/warning');
    else {
      
      $sCollection = $this->escape($this->getAbsolutePath($sCollection, $sBase));
      $sResult = "collection($sCollection)";
    }
    
    return $sResult;
  }
  
  public function callDocument($sDocument, $sBase = '') {
    
    $sResult = '';
    
    if (!$sDocument) dspm('Empty name is not allowed for document creation', 'db/warning');
    else {
      
      $sDocument = $this->escape($this->getAbsolutePath($sDocument, $sBase));
      $sResult = "doc($sDocument)";
    }
    
    return $sResult;
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
  
  public function update($sQuery, array $aNamespaces = array()) {
    
    return $this->query($sQuery, $aNamespaces, false, false);
  }
  
  /*
  public static function importDatabase() {
    
    $xDirectory = 'database/export';
    $xName = $xDirectory.'/@name';
    
    if ((!$sPath = self::getSettings($xDirectory)) || (!$sName = self::getSettings($xName))) {
      
      dspm(xt('Chemin %s inexistant ou invalide pour l\'importation dans le fichier root', new HTML_Strong($xDirectory)), 'warning');
      
    } else {
      
      self::cleanDocument($sPath.'/'.$sName);
      
      if ($oFile = self::getFile($sPath.'/'.$sName, true)) {
        
        $oDocument = $oFile->getDocument();
        
        if ($oDocument->isEmpty()) dspm(xt('Le document d\'importation %s est vide', new HTML_Strong), 'warning');
        else {
          
          self::getDatabase()->run("delete $sName");
          self::getDatabase()->run('add '.$oFile->getSystemPath());
          
          dspm(xt('Base de donnée importée depuis %s', $oDocument->getFile()->parse()), 'success');
        }
      }
    }
    
    return '';
  }
  
  public static function exportDatabase() {
    
    $xDirectory = 'database/export';
    $xName = $xDirectory.'/@name';
    
    if ((!$sPath = self::getSettings($xDirectory)) || (!$sName = self::getSettings($xName))) {
      
      dspm(xt('Chemin %s inexistant ou invalide pour l\'exportation dans le fichier root', new HTML_Strong($xDirectory)), 'warning');
      
    } else {
      
      if ($sPath{0} != '/') $sResultPath = self::getDirectory()->getSystemPath().'/'.$sPath;
      else $sResultPath = $sPath;
      
      self::getDatabase()->run("export $sResultPath $sName");
      
      self::cleanDocument($sPath.'/'.$sName);
      
      dspm(xt('Données exportées dans %s', new HTML_Strong($sResultPath.'/'.$sName)), 'success');
      
      
    }
    
    return '';
  }
  */

  
  public function __destruct() {
    
    if ($this->oSession && !$this->getSession()->disconnect()) {
      //dspm(xt('Erreur pendant la déconnexion : %s', $this->getError()), 'db/error');
    }
  }
}

