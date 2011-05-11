<?php

class ModuleBase {
  
  protected $oSchema = null;  
  
  // array of classe's object to use within this class with $this->create() loaded in [settings]/classes
  private $aClasses = array();
  
  private $aNamespaces = array();
  private $sNamespace = '';
  private $sPrefix = '';
  
  private $sName = '';
  
  private $oDirectory = null;
  private $oArguments = null;
  
  protected function setDirectory($mPath) {
    
    if (is_string($mPath)) $this->oDirectory = extractDirectory($mPath);
    else $this->oDirectory = $mPath;
  }
  
  public function getDirectory() {
    
    return $this->oDirectory;
  }
  
  public function create($sName, $aArguments = array()) {
    
    $result = null;
    
    if (!$this->getArguments()) {
      
      $this->dspm(xt('Cannot build object %s. No settings defined'),
        new HTML_Strong($sName), 'action/error');
    }
    else if (!$aClass = $this->getArgument('classes/' . $sName)) { // has class ?
      
      dspm(xt('Cannot build object %s. No settings defined for these class'),
        new HTML_Strong($sKey), 'action/error');
    }
    else {
      
      // set absolute path for relative classe file's path
      
      if ($aClass['file'] && $aClass['file'][0] != '/' && ($sPath = $this->getArgument('path'))) {
        
        $aClass['file'] = Controler::getAbsolutePath($aClass['file'], $this->getDirectory());
      }
      
      $result = Controler::createObject($aClass, $aArguments);
    }
    
    return $result;
  }
  
  protected function setName($sName) {
    
    return $this->sName = $sName;
  }
  
  public function getName() {
    
    return $this->sName;
  }
  
  protected function setArguments(array $aArguments = null, $bMerge = true) {
    
    if ($aArguments) {
      
      if ($this->getArguments() && $bMerge) $this->getArguments()->merge($aArguments);
      else $this->oArguments = new Arguments($aArguments, $this->getName());
    }
    else $this->oArguments = null;
    
    return $this->getArguments();
  }
  
  protected function getArguments() {
    
    return $this->oArguments;
  }
  
  protected function &getArgument($sPath, $mDefault = null, $bDebug = true) {
    
    if ($this->getArguments()) return $this->getArguments()->get($sPath, $mDefault, $bDebug);
    else return $mDefault;
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
  
  protected function getSchema() {
    
    return $this->oSchema;
  }
  
  protected function mergeNamespaces(array $aNamespaces = array()) {
    
    if ($aNamespaces) return array_merge($this->getNS(), $aNamespaces);
    else return $this->getNS();
  }
  
  protected function setNamespace($sUri, $sPrefix = '', $bDefault = true) {
    
    $this->aNamespaces[$sPrefix] = $sUri;
    
    if ($bDefault) {
      
      $this->sNamespace = $sUri;
      $this->sPrefix = $sPrefix;
    }
  }
  
  protected function setNamespaces(array $aNS) {
    
    $this->aNamespaces = $aNS;
  }
  
  public function getNS($sPrefix = null) {
    
    if ($sPrefix) return array($sPrefix => array_val($sPrefix, $this->aNamespaces));
    else return $this->aNamespaces;
  }
  
  public function getNamespace($sPrefix = null) {
    
    if ($sPrefix) return array_val($sPrefix, $this->aNamespaces);
    else return $this->sNamespace;
  }
  
  public function setPrefix($sPrefix) {
    
    $this->sPrefix = $sPrefix;
  }
  
  public function getPrefix() {
    
    return $this->sPrefix;
  }
  
  protected function dspm($mMessage, $sStatut = SYLMA_MESSAGES_DEFAULT_STAT) {
    
    $oPath = new HTML_Div(xt('Module %s -&gt; %s', view($this->getName()), new HTML_Strong($this->getDirectory())),
      array('style' => 'font-weight: bold; padding: 5px 0 5px;'));
    return dspm(array($oPath, $mMessage, new HTML_Tag('hr')), $sStatut);
  }
}


