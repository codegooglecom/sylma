<?php

class XML_Directory extends XML_Resource {
  
  private $aDirectories = array();
  private $aFiles = array();
  private $aFreeFiles = array();
  private $oSettings = null;
  
  private $aChildrenRights = null;
  
  public function __construct($sPath, $sName, $aRights = array(), $oParent = null) {
    
    $this->sFullPath = $sName ? $sPath.'/'.$sName : $sPath;
    
    if (is_dir(MAIN_DIRECTORY.$this->getFullPath())) {
      
      $this->aRights = $this->aChildrenRights = $aRights;
      $this->sName = $sName;
      $this->sPath = $sPath;
      $this->oParent = $oParent;
      
      $this->doExist(true);
      $this->loadRights();
    }
  }
  
  private function getChildrenRights() {
    
    return $this->aChildrenRights;
  }
  
  /**
   * Get security XML_Document (eg: directory.sml)
   * @param boolean $bRecursive Get last setting file from parents
   * @return XML_SFile|null
   */
  public function getSettings($bRecursive = false) {
    
    if ($bRecursive && !$this->oSettings) {
      
      if ($this->getParent()) return $this->getParent()->getSettings(true);
      else dspm(t('Aucun fichier de sécurité dans le répertoire parent'), 'file/error');
    }
    
    return $this->oSettings;
  }
  
  private function loadRights() {
    
    if (!$this->isSecured()) {
      
      if (Controler::getUser()) {
        
        $this->oSettings = new XML_SFile($this);
        
        if ($this->getSettings()->getDocument()) { // read security file
          
          // self rights
          
          if ($aRights = $this->setRights($this->getSettings()->getDirectory())) $this->aChildrenRights = $aRights;
          
          // children rights
          
          if (($oChildrenRights = $this->getSettings()->getPropagation()) &&
            ($aChildrenRights = $this->extractRights($oChildrenRights))) $this->aChildrenRights = $aChildrenRights;
          
        } else $this->setRights();
        
      } else if (Controler::useStatut('file/report')) Controler::addMessage(xt('Sécurisation suspendue dans "%s"', new HTML_Strong($this->getFullPath())), 'file/report');
    }
  }
  
  public function unbrowse() {
    
    $oResult = $this->parseXML();
    $oParent = $this;
    
    while ($oParent = $oParent->getParent()) {
      
      $oResult->shift($oParent->parseXML());
    }
    
    return $oResult;
  }
  
  public function browse($aExtensions, $aPaths = array(), $iDepth = null) {
    
    $aFiles = scandir(MAIN_DIRECTORY.$this->getFullPath(), 0);
    $oElement = $this->parseXML();
    
    if ($iDepth === null || $iDepth > 0) {
      
      if ($iDepth) $iDepth--;
      
      foreach ($aFiles as $sFile) {
        
        if ($sFile != '.' && $sFile != '..') {
          
          if (($oFile = $this->getFile($sFile)) && $oFile->getUserMode() != 0) {
            
            if ($aExtensions && !in_array(strtolower($oFile->getExtension()), $aExtensions)) $oFile = null;
            else $oElement->add($oFile->parseXML());
            
          } else if ($oDirectory = $this->getDirectory($sFile)) {
            
            $bValid = true;
            
            foreach ($aPaths as $sPath) {
              
              switch ($sPath{0}) {
                
                case '/' : if ($sPath == $oDirectory->getFullPath()) $bValid = false; break;
                default : if ($sPath == $oDirectory->getName()) $bValid = false; break;
              }
            }
            
            if ($bValid) {
              
              if ($iDepth === null || $iDepth) $oElement->add($oDirectory->browse($aExtensions, $aPaths, $iDepth));
              else $oElement->add($oDirectory->parseXML());
            }
          }
        }
      }
    }
    
    //if ($oElement->isEmpty() && $this->getUserMode() != 0) return null;
    //else 
    return $oElement;
  }
  
  /**
   * Add a file into this directory via XML_Document->freeSave()
   */
  
  public function addFreeDocument($sName, XML_Document $oDocument) {
    
    $oDocument->saveFree($this, $sName);
  }
  
  /*
   * Browse then return list of files inside the directory and sub-directories if iDepth != null
   */
  public function getFiles(array $aExtensions = array(), $sPreg = null, $iDepth = 0) {
    
    $this->browse(array(), array(), 1);
    $aResult = array();
    
    // Files of current directory
    
    if ($aExtensions) {
      
      foreach ($this->aFiles as $sFile => $oFile) {
        
        if ($oFile &&
          (!$aExtensions || in_array(strtolower($oFile->getExtension()), $aExtensions)) &&
          (!$sPreg || preg_match($sPreg, $sFile))) $aResult[] = $oFile;
      }
      
    } else $aResult = array_values($this->aFiles);
    
    // Recursion in sub-directory
    
    if ($iDepth === null || $iDepth > 0) {
      
      if ($iDepth) $iDepth--;
      
      foreach ($this->aDirectories as $oDirectory) {
        
        if ($oDirectory) $aResult = array_merge($aResult, $oDirectory->getFiles($aExtensions, $sPreg, $iDepth));
      }
    }
    
    return $aResult;
  }
  
  /**
   * Unload then reload file/document, maybe TODO to optimize by keeping the doc
   */
  public function updateFile($sName) {
    
    if (array_key_exists($sName, $this->aFiles)) unset($this->aFiles[$sName]);
    return $this->getFile($sName);
  }
  
  /**
   * Unload then reload directory
   */
  public function updateDirectory($sName) {
    
    if (array_key_exists($sName, $this->aDirectories)) unset($this->aDirectories[$sName]);
    return $this->getDirectory($sName);
  }
  
  /**
   * Build an XML_File, check existenz and right access
   * @param $sName The name + extension of the file
   * @param $bDebug If true, send an error message if no access is found
   * @return XML_File the file requested
   */
  public function getFile($sName, $bDebug = false) {
    
    $this->loadRights();
    
    if ($sName && is_string($sName)) {
      
      if (!array_key_exists($sName, $this->aFiles)) {
        
        $oFile = new XML_File($this->getFullPath(), $sName, $this->getRights(), $this, $bDebug);
        
        if ($oFile->doExist()) {
          
          if (!$mRights = $this->getSettings()->getFile($sName)) $mRights = $this->getChildrenRights();
          
          //if (((string) $sName) == 'root.xml') print_r($mRights);
          $oFile->setRights($mRights);
          
          if (Controler::getUser()) $this->aFiles[$sName] = $oFile;
          else return $oFile;
          
        } else $this->aFiles[$sName] = null;
      }
      
      return $this->aFiles[$sName];
    }
    
    return null;
  }
  
  public function getDirectory($sName) {
    
    $this->loadRights();
    
    if ($sName == '.') return $this;
    else if ($sName == '..') return $this->getParent();
    else if ($sName) {
      
      if (!array_key_exists($sName, $this->aDirectories)) {
        
        $oDirectory = new XML_Directory($this->getFullPath(), $sName, $this->getChildrenRights(), $this);
        
        if ($oDirectory->doExist()) $this->aDirectories[$sName] = $oDirectory;
        else $this->aDirectories[$sName] = null;
      }
      
      return $this->aDirectories[$sName];
    }
    
    return null;
  }
  
  public function getDistantFile($aPath, $bDebug = false) {
    
    if ($aPath) {
      
      if (count($aPath) == 1) {
        
        return $this->getFile($aPath[0], $bDebug);
        
      } else {
        
        $sName = array_shift($aPath);
        
        $oSubDirectory = $this->getDirectory($sName);
        
        if ($oSubDirectory) return $oSubDirectory->getDistantFile($aPath, $bDebug);
      }
    }
    
    return null;
  }
  
  public function getDistantDirectory($mPath) {
    
    if (is_string($mPath)) $mPath = explode('/', $mPath);
    
    if ($mPath) {
      
      $sName = array_shift($mPath);
      
      if ($oSubDirectory = $this->getDirectory($sName)) return $oSubDirectory->getDistantDirectory($mPath);
      
    } else return $this;
    
    return null;
  }
  
  public function addDirectory($sName) {
    
    $oDirectory = null;
    
    if (!$oDirectory = $this->getDirectory($sName)) {
      
      if ($sName && $this->checkRights(MODE_WRITE)) {
        
        $sPath = MAIN_DIRECTORY.$this.'/'.$sName;
        
        mkdir($sPath, SYLMA_DEFAULT_MODE);
        
        unset($this->aDirectories[$sName]);
        $oDirectory = $this->getDirectory($sName);
        
        //dspm(xt('Création du répertoire %s', new HTML_Strong($oDirectory)), 'file/notice');
        
        //} else dspm(xt('Création du répertoire %s impossible', new HTML_Stong($this.$sName)), 'file/error');
      }
    }
    
    return $oDirectory;
  }
  
  public function checkRights($iMode) {
    
    $this->loadRights();
    
    if (!$this->isSecured() || ($iMode & $this->getUserMode())) return true;
    
    return false;
  }
  
  /**
   * Change rights in corresponding SECURITY_FILE
   */
  public function updateRights($sOwner, $sGroup, $sMode) {
    
    if ($this->checkRightsArguments($sOwner, $sGroup, $sMode)) {
      
      return $this->getSettings()->updateDirectory($sOwner, $sGroup, $sMode);
    }
    
    return false;
  }
  
  public function rename($sNewName) {
    
    $oResult = null;
    
    if ($this->checkRights(MODE_WRITE)) {
      
      if (rename($this->getRealPath(), $this->getParent()->getRealPath().'/'.$sNewName)) {
        
        $oResult = $this->getParent()->updateDirectory($sNewName);
        Controler::addMessage(t('Répertoire renommé !'), 'success');
        
      } else Controler::addMessage(t('Impossible de renommer le répertoire !'), 'warning');
    }
    
    return $oResult;
  }
  
  public function getSystemPath() {
    
    return Controler::getSystemPath().'/'.$this->getRealPath();
  }
  
  public function getRealPath() {
    
    return $this->getParent() ? MAIN_DIRECTORY.$this : MAIN_DIRECTORY;
  }
  
  public function delete($bDeleteChildren = false) {
    
    $bResult = false;
    
    if ($this->checkRights(MODE_WRITE)) {
      
      if ($bDeleteChildren) {
        
        if ($this === Controler::getDirectory()) dspm('Impossible de supprimer le répertoire principal !', 'file/error');
        else {
          
          $this->browse(array(), array(), 1);
          
          foreach ($this->aFiles as $oFile) if ($oFile) $oFile->delete();
          foreach ($this->aDirectories as $oDirectory) $oDirectory->delete(true);
        }
      }
      
      $bResult = rmdir(MAIN_DIRECTORY.$this);
      
      $this->getParent()->updateDirectory($this->getName());
    }
    
    return $bResult;
  }
    
  public function parseXML() {
    
    if (!$sName = xmlize($this->getName())) {
      
      $sName = t('<racine>');
      $sPath = ''; //'/';
      
    } else $sPath = $this->getFullPath();
    
    return new XML_Element('directory', null, array(
      'full-path' => xmlize($sPath),
      'owner' => $this->getOwner(),
      'group' => $this->getGroup(),
      'mode' => $this->getMode(),
      'read' => booltostr($this->checkRights(MODE_READ)),
      'write' => booltostr($this->checkRights(MODE_WRITE)),
      'execution' => booltostr($this->checkRights(MODE_EXECUTION)),
      'name' => $sName));
  }
  
  public function parse() {
    
    return new HTML_A(Controler::getSettings('module[@name="explorer"]/path').'?path='.$this->getFullPath(), $this->getFullPath());
  }
  
  public function __toString() {
    
    if ($this->getFullPath()) return $this->getFullPath();
    else return '';//'/';
  }
}

