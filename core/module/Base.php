<?php

class ModuleBase {
  
  protected $oSchema = null;  
  
  // array of classe's object to use within this class with $this->create() loaded in [settings]/classes
  protected $aClasses = array();
  
  private $aNamespaces = array();
  private $sNamespace = '';
  private $sPrefix = '';
  
  private $sName = '';
  
  private $oDirectory = null;
  private $arguments = null;
  
  protected function setName($sName) {
    
    return $this->sName = $sName;
  }
  
  public function getName() {
    
    return $this->sName;
  }
  
  protected function setDirectory($mPath) {
    
    if (is_string($mPath)) $this->oDirectory = extractDirectory($mPath);
    else $this->oDirectory = $mPath;
  }
  
  public function getDirectory() {
    
    return $this->oDirectory;
  }
  
  protected function getFile($sPath) {
    
    return Controler::getFile(Controler::getAbsolutePath($sPath, $this->getDirectory()));
  }
  
  public function create($sName, $aArguments = array()) {
    
    $result = null;
    
    if (!$this->getArguments()) {
      
      $this->log(txt('Cannot build object @class %s. No settings defined', $sName));
    }
    else if (!$aClass = $this->getArgument('classes/' . $sName)) { // has class ?
      
      $this->log(txt('Cannot build object @class %s. No settings defined for these class', $sName));
    }
    else {
      
      // set absolute path for relative classe file's path
      
      if (array_key_exists('file', $aClass) && $aClass['file'] && $aClass['file'][0] != '/' && ($sPath = $this->getArgument('path'))) {
        
        $aClass['file'] = Controler::getAbsolutePath($aClass['file'], $this->getDirectory());
      }
      
      $result = Controler::createObject($aClass, $aArguments);
    }
    
    return $result;
  }
  
  protected function setArguments($mArguments = null, $bMerge = true) {
    
    if ($mArguments) {
      
      if (is_string($mArguments)) {
        
        if ($file = $this->getFile($mArguments)) $mArguments = $file->getYAML();
      }
      
      if ($this->getArguments() && $bMerge) $this->getArguments()->merge($mArguments);
      else $this->arguments = new Arguments($mArguments, $this->getName());
    }
    else $this->arguments = null;
    
    return $this->getArguments();
  }
  
  protected function getArguments() {
    
    return $this->arguments;
  }
  
  protected function getArgument($sPath, $mDefault = null, $bDebug = true) {
    
    $mResult = $mDefault;
    
    if ($this->getArguments()) {
      
      $mResult = $this->getArguments()->get($sPath, $bDebug);
      if (!$mResult && $mDefault !== 'null') $mResult = $mDefault;
    }
    
    return $mResult;
  }
  
  protected function setSchema($mSchema, $bNamespace = true, $sPrefix = '') {
    
    if (is_string($mSchema)) $mSchema = $this->getDocument($mSchema);
    
    if ($mSchema && !$mSchema->isEmpty()) { // !$this->getNamespace() && TODO REM
      
      if ($sNamespace = $mSchema->getAttribute('targetNamespace')) {
        
        if (!$sPrefix) $sPrefix = $this->getPrefix();
        $this->setNamespace($sNamespace, $sPrefix, $bNamespace);
      }
      
      $this->oSchema = $mSchema;
    }
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
  
  /*
   * Add a log message with the @class Logger
   * @param mixed|DOMNode|string|array $mMessage The message to send, will be parsed or stringed
   * @param $sStatut The statut of message : see @file /system/allowed-messages.xml for more infos
   **/
  protected function log($mMessage, $sStatut = Sylma::LOG_STATUT_DEFAULT) {
    
    return Sylma::log($this->getNamespace(), $mMessage, $sStatut);
  }
  /**
   * Alias of log for ascendent compatibility
   */
  protected function dspm($mMessage, $sStatut = Sylma::LOG_STATUT_DEFAULT) {
    
    $oPath = new HTML_Div(xt('Module %s -&gt; %s', view($this->getName()), new HTML_Strong($this->getDirectory())),
      array('style' => 'font-weight: bold; padding: 5px 0 5px;'));
    return dspm(array($oPath, $mMessage, new HTML_Tag('hr')), $sStatut);
  }
}


