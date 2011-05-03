<?php

class ModuleExtension extends Module {
  
  // All the modules directories listed in the extends stack, added with @method addDirectory()
  // concerned methods are getDirectory(), getFile(), getDocument() and runAction()
  private $aDirectories = array();
  
  protected function setName($sName) {
    
    if (!$this->getName()) parent::setName($sName);
  }
  
  /**
   * Set the working directory for this module. It can be retrieve with @method getDirectory()
   * Multiple directories can be used, see @method addDirectory() for more details
   * @param string $sPath The path to directory, mainly read with __file__ and send to @function extractDirectory()
   */
  protected function setDirectory($mPath) {
    
    $this->aDirectories[] = array();
    $this->addDirectory($mPath);
  }
  
  /**
   * Add a directory to the directories stack. When loading a file or an action,
   * directories will be scan from last to first to find corresponding file.
   * @return XML_Directory The current module directory
   */
  protected function addDirectory($mPath, $sClass = '') {
    
    if (!$sClass) $sClass = get_class($this);
    if (is_string($mPath)) $mPath = extractDirectory($mPath, true);
    
    $this->aDirectories[$sClass] = $mPath;
  }
  
  /**
   * Load one of the working directories
   * @param string $sClass The corresponding class name
   * @return XML_Directory If $sClass is set, look for the directory corresponding to this class in directories stack
   *  else return last directory
   */
  public function getDirectory($sClass = '') {
    
    if ($sClass) return array_val($sClass, $this->getDirectories());
    else return array_last($this->getDirectories());
  }
  
  protected function getDirectories() {
    
    return $this->aDirectories;
  }
  
  protected function getFile($sPath, $bDebug = true) {
    
    $oFile = null;
    $aReverse = array_reverse($this->getDirectories());
    
    foreach ($aReverse as $oDirectory) {
      
      if ($oFile = Controler::getFile(Controler::getAbsolutePath($sPath, $oDirectory))) {
        
        break;
      }
    }
    
    if (!$oFile && $bDebug) {
      
      $this->dspm(xt('Fichier %s introuvable', new HTML_Strong($sPath)), 'file/warning');
    }
    
    return $oFile;
  }
  
  protected function getTemplate($sPath) {
    
    if ($oFile = $this->getFile($sPath, false)) return parent::getTemplate((string) $oFile);
    else return null;  
  }
  
  protected function getDocument($sPath, $iMode = MODE_READ) {
    
    if ($oFile = $this->getFile($sPath, false)) return parent::getDocument((string) $oFile, $iMode);
    else return null;
  }
  
  protected function runAction($sPath, $aArguments = array()) {
    
    $aReverse = array_reverse($this->getDirectories());
    
    foreach ($aReverse as $oDirectory) {
      
      $sRealPath = Controler::getAbsolutePath($sPath, $oDirectory);
      $oPath = new XML_Path($sRealPath, $aArguments, true, false, false);
      
      if ($oPath->getPath()) break;
    }
    
    if ($oPath->getPath()) return new XML_Action($oPath);
    return null;
  }
}


