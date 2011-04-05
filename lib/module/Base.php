<?php

class ModuleBase {
  
  protected $oSchema = null;  
  
  private $aNamespaces = array();
  private $sNamespace = '';
  private $sPrefix = '';
  
  private $sName = '';
  
  private $oDirectory = null;
  private $oArguments = null;
  
  protected function setDirectory($sPath) {
    
    $this->oDirectory = extractDirectory($sPath);
  }
  
  protected function getDirectory() {
    
    return $this->oDirectory;
  }
  
  protected function setName($sName) {
    
    $this->sName = $sName;
  }
  
  public function getName() {
    
    return $this->sName;
  }
  
  protected function setArguments(array $aArguments) {
    
    $this->oArguments = new Arguments($aArguments);
  }
  
  protected function getArguments() {
    
    return $this->oArguments;
  }
  
  protected function readArgument($sPath, $mDefault = null, $bDebug = true) {
    
    if ($this->getArguments()) return $this->getArguments()->read($sPath, $mDefault, $bDebug);
    else return $mDefault;
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
  
  protected function mergeNamespaces($aNamespaces = array()) {
    
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
