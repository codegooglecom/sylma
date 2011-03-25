<?php

class Module {
  
  protected $oDirectory = null;
  
  private $oSettings = null;  // global module settings
  private $oOptions = null;  // contextual settings
  
  protected $oSchema = null;  
  private $sName = '';
  
  private $aNamespaces = array();
  private $sNamespace = '';
  private $sPrefix = '';
  
  public function setDirectory($sPath) {
    
    $this->oDirectory = extractDirectory($sPath, true);
  }
  
  public function getSettings($sPath = '') {
    
    if (!$this->oSettings && $this->getName()) {
      
      $this->oSettings = Controler::getSettings()->get("module[@name='{$this->getName()}']");
    }
    
    if ($sPath && $this->oSettings) return $this->oSettings->read($sPath);
    else return $this->oSettings;
  }
  /*
  protected function getOptions() {
    
    return $this->oOptions;
  }
  
  protected function getOption($sName, $bDebug = true) {
    
    $oResult = null;
    
    if (!$this->getOptions()) $this->dspm(xt('Aucune option disponible pour le module'), 'action/warning');
    else {
      
      $oResult = $this->getOptions()->getByName($sName);
      
      if ($bDebug && !$oResult)
        $this->dspm(xt('Option %s introuvable dans %s', new HTML_Strong($sPath), view($this->getOptions())), 'action/warning');
    }
    
    return $oResult;
  }
  
  protected function readOption($sPath, $bDebug = true) {
    
    if ($oOption = $this->getOption($sPath, $bDebug)) return $oOption->read();
    else return '';
  }
  */
  protected function setName($sName) {
    
    $this->sName = $sName;
  }
  
  public function getName() {
    
    return $this->sName;
  }
  
  protected function setSchema($oSchema, $bNamespace = false, $sPrefix = '') {
    
    if ($bNamespace && !$this->getNamespace() && $oSchema && !$oSchema->isEmpty()) {
      
      if ($sNamespace = $oSchema->getAttribute('targetNamespace')) {
        
        if (!$sPrefix) $sPrefix = $this->getPrefix();
        $this->setNamespace($sNamespace, $sPrefix, true);
      }
    }
    
    $this->oSchema = $oSchema;
  }
  
  public function getSchema() {
    
    return $this->oSchema;
  }
  
  public function getDirectory() {
    
    return $this->oDirectory;
  }
  
  protected function setNamespace($sUri, $sPrefix = '', $bDefault = true) {
    
    $this->aNamespaces[$sPrefix] = $sUri;
    
    if ($bDefault) {
      
      $this->sNamespace = $sUri;
      $this->sPrefix = $sPrefix;
    }
  }
  
  public function getNS($sPrefix = null) {
    
    if ($sPrefix) return array($sPrefix => array_val($sPrefix, $this->aNamespaces));
    else return $this->aNamespaces;
  }
  
  public function getNamespace($sPrefix = null) {
    
    if ($sPrefix) return array_val($sPrefix, $this->aNamespaces);
    else return $this->sNamespace;
  }
  
  public function getFullPrefix() {
    
    return $this->getPrefix() ? $this->getPrefix().':' : '';
  }
  
  public function getPrefix() {
    
    return $this->sPrefix;
  }
  
  public function runAction($sPath, $aArguments = array()) {
    
    $sPath = Controler::getAbsolutePath($sPath, $this->getDirectory());
    $oPath = new XML_Path($sPath, $aArguments, true, false);
    
    return new XML_Action($oPath);
  }
  
  public function getDocument($sPath, $bXSL = false) {
    
    if ($oFile = Controler::getFile(Controler::getAbsolutePath($sPath, $this->getDirectory()))) {
      
      if ($bXSL) return new XSL_Document((string) $oFile, MODE_EXECUTION);
      else return $oFile->getDocument();
      
    } else return null;
  }
  
  public function dspm($mMessage, $sStatut = SYLMA_MESSAGES_DEFAULT_STAT) {
    
    $oPath = new HTML_Div(xt('Module %s -&gt; %s', view($this->getName()), new HTML_Strong($this->getDirectory())),
      array('style' => 'font-weight: bold; padding: 5px 0 5px;'));
    return dspm(array($oPath, $mMessage, new HTML_Tag('hr')), $sStatut);
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


