<?php

require_once('Manager.php');

class ModuleBase extends ModuleManager {
  
  private $oSchema = null;  
  private $oDirectory = null;
  
  protected function setDirectory($mPath) {
    
    if (is_string($mPath)) $this->oDirectory = extractDirectory($mPath);
    else $this->oDirectory = $mPath;
  }
  
  public function getDirectory() {
    
    return $this->oDirectory;
  }
  
  /**
   * Get a file object relative to the module's directory set in @method setDirectory()
   *
   * @param string $sPath The relative or absolute path to the file
   * @return storage\fs\file|null The file corresponding to the path given, or NULL if none found
   */
  protected function getFile($sPath) {
    
    return Controler::getFile(Controler::getAbsolutePath($sPath, $this->getDirectory()));
  }
  
  /**
   * Build an object defined in @settings classes
   * 
   * @param string $sName The short name of the class
   * @param array $aArguments The arguments sent to the object on contstruction
   *
   * @return mixed The object builded
   */
  protected function setSchema($mSchema, $bNamespace = true, $sPrefix = '') {
    
    if (is_string($mSchema)) $mSchema = $this->getDocument($mSchema, Sylma::MODE_EXECUTE);
    
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
  
  /**
   * Alias of log for ascendent compatibility
   */
  protected function dspm($mMessage, $sStatut = Sylma::LOG_STATUT_DEFAULT) {
    
    $oPath = new HTML_Div(xt('Module %s -&gt; %s', view($this->getNamespace()), new HTML_Strong($this->getDirectory())),
      array('style' => 'font-weight: bold; padding: 5px 0 5px;'));
    return dspm(array($oPath, $mMessage, new HTML_Tag('hr')), $sStatut);
  }
}


