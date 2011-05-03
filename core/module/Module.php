<?php

class Module extends ModuleBase {
  
  private $oSettings = null;  // global module settings
  private $oOptions = null;  // contextual settings
  
  protected function readSettings($sPath = '', $mDefault = '', $bDebug = true) {
    
    $sResult = $mDefault;
    
    if ($nResult = $this->getSettings($sPath, $bDebug)) {
      
      if (is_object($nResult)) $sResult = $nResult->read();
      else $sResult = (string) $nResult;
    }
    if (!$sResult) $sResult = $mDefault;
    
    return $sResult;
  }
  
  protected function getSettings($sPath = '', $mDefault = null, $bDebug = true) {
    
    if (!$this->oSettings) {
      
      // try to load from the name
      
      if ($this->getName() && ($oSettings = Controler::getSettings()->get("module[@name='{$this->getName()}']"))) {
        
        $this->oSettings = new Options(new XML_Document($oSettings), null); // TODO, schemas and namespaces
      }
    }
    
    if ($sPath && $this->oSettings) return $this->oSettings->get($sPath, $bDebug);
    else if ($mDefault) return $mDefault;
    else return $this->oSettings;
  }
  
  protected function setSettings(XML_Document $dSettings, XML_Document $dSchema = null, array $aNS = array()) {
    
    $this->oSettings = new Options($dSettings, $dSchema, $this->mergeNamespaces($this->getNS(), $aNS));
  }
  
  protected function runAction($sPath, $aArguments = array()) {
    
    $sPath = Controler::getAbsolutePath($sPath, $this->getDirectory());
    $oPath = new XML_Path($sPath, $aArguments, true, false);
    
    return new XML_Action($oPath);
  }
  
  protected function getTemplate($sPath) {
    
    if ($oFile = $this->getFile($sPath)) return new XSL_Document((string) $oFile, MODE_EXECUTION);
    else return null;
  }
  
  protected function getFile($sPath) {
    
    return Controler::getFile(Controler::getAbsolutePath($sPath, $this->getDirectory()));
  }
  
  protected function getDocument($sPath, $iMode = MODE_READ) {
    
    if ($oFile = $this->getFile($sPath)) return new XML_Document((string) $oFile, $iMode);
    else return null;
  }
  
  /*** Options ***/
  
  protected function setOptions(XML_Document $oOptions, XML_Document $oSchema = null, $aNS = array()) {
    
    $this->oOptions = new Options($oOptions, $oSchema, $this->mergeNamespaces($this->getNS(), $aNS));
    
    return $this->getOptions();
  }
  
  protected function getOptions() {
    
    return $this->oOptions;
  }
  
  protected function getOption($sPath, $mDefault = null, $bDebug = false) {
    
    if ($this->getOptions() !== null) $eResult = $this->getOptions()->get($sPath, $bDebug);
    return isset($eResult) ? $eResult : $mDefault;
  }
  
  protected function readOption($sPath, $mDefault = null, $bDebug = false) {
    
    if ($this->getOptions() !== null) $sResult = $this->getOptions()->read($sPath, $bDebug);
    return $sResult ? $sResult : $mDefault;
  }
  
  public function getFullPrefix() {
    
    return $this->getPrefix() ? $this->getPrefix().':' : '';
  }
}

