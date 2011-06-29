<?php

/**
 * Simple generic class intended to be extends by about every modules used with Sylma
 * 
 * - Settings in DOM Document with @method getSettings() (global settings) and @method getOptions() (context settings)
 * - Main directory relative calls (actions, documents, templates)
 */
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
  
  /**
   * Load an XSL Template from a path relative to the module's directory
   * 
   * @param string $sPath The path to the template, relative to the module's directory
   * @return null|DOMDocument The loaded template, or null if not found/valid
   */
  protected function getTemplate($sPath) {
    
    if ($oFile = $this->getFile($sPath)) return new XSL_Document((string) $oFile, MODE_EXECUTION);
    else return null;
  }
  
  /**
   * Load a DOM Document from a path relative to the module's directory
   * 
   * @param string $sPath The path to the document, relative to the module's directory
   * @param integer $iMode The load mode (READ, WRITE, EXECUTE)
   * 
   * @return null|DOMDocument The loaded document, or null if not found/valid
   */
  protected function getDocument($sPath, $iMode = Sylma::MODE_READ) {
    
    if ($oFile = $this->getFile($sPath)) return new XML_Document((string) $oFile, $iMode);
    else return null;
  }
  
  protected function setOptions(XML_Document $oOptions, XML_Document $oSchema = null, $aNS = array()) {
    
    $this->oOptions = new Options($oOptions, $oSchema, $this->mergeNamespaces($this->getNS(), $aNS));
    
    return $this->getOptions();
  }
  
  protected function getOptions() {
    
    return $this->oOptions;
  }
  
  /**
   * Return a setting result from @interface SettingsInterface object set with @method setOptions()
   *
   * @param string $sPath The path to the value wanted
   * @param mixed The default value to return if no value is found
   * @param boolean If set to TRUE, an exception will be sent
   *
   * @return mixed The value found at the location of @param $sPath or null if not found
   */
  protected function getOption($sPath, $mDefault = null, $bDebug = false) {
    
    $result = null;
    
    if ($this->getOptions() !== null) $result = $this->getOptions()->get($sPath, $bDebug);
    return $result ? $result : $mDefault;
  }
  
  /**
   * Return a string formated option read with @method getOptions()
   *
   * @param string $sPath The path to the value wanted
   * @param mixed The default value to return if no value is found
   * @param boolean If set to TRUE, an @interface SylmaExceptionInterface object will be sent
   *
   * @return string|null The value found at the location of @param $sPath or null if not found
   */
  protected function readOption($sPath, $mDefault = null, $bDebug = false) {
    
    $sResult = null;
    
    if ($this->getOptions() !== null) $sResult = $this->getOptions()->read($sPath, $bDebug);
    return isset($sResult) ? $sResult : $mDefault;
  }
  
  public function getFullPrefix() {
    
    return $this->getPrefix() ? $this->getPrefix().':' : '';
  }
}

