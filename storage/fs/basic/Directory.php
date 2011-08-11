<?php

namespace sylma\storage\fs\basic;
use \sylma\dom, \sylma\storage\fs;

require_once('Resource.php');
require_once('storage/fs/directory.php');

class Directory extends Resource implements fs\directory {
  
  const NS = 'http://www.sylma.org/storage/fs/basic/directory';
  
  private $aDirectories = array();
  private $aFiles = array();
  private $aFreeFiles = array();
  private $settings = null;
  
  private $aChildrenRights = null;
  
  public function __construct($sPath, $sName, array $aRights = array(), fs\directory $parent = null, fs\controler $controler = null) {
    
    $this->sFullPath = $sName ? $sPath.'/'.$sName : $sPath;
    $this->controler = $controler;
    
    if (is_dir(\Sylma::ROOT . $this->getFullPath())) {
      
      $this->aRights = $this->aChildrenRights = $aRights;
      $this->sName = $sName;
      $this->sPath = $sPath;
      $this->parent = $parent;
      
      $this->doExist(true);
      if ($parent) $this->loadRights();
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
    
    if ($bRecursive && !$this->settings) {
      
      if ($this->getParent()) return $this->getParent()->getSettings(true);
      else dspm(t('Aucun fichier de sécurité dans le répertoire parent'), 'file/error');
    }
    
    return $this->settings;
  }
  
  private function loadRights() {
    
    if (!$this->isSecured() && \Controler::getUser()) {
      
      $this->settings = $this->getControler()->create('security', array($this, $this->getControler()));
      
      $aRights = $this->getSettings()->getDirectory();
      
      // self rights
      if ($aRights) $this->aChildrenRights = $aRights;
      
      // children rights
      if ($aChildrenRights = $this->getSettings()->getPropagation()) {
        
        $this->aChildrenRights = $aChildrenRights;
      }
      
      $this->setRights($aRights);
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
    
    $aFiles = scandir(\Sylma::ROOT.$this->getFullPath(), 0);
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
  
  /*
   * Browse then return list of files inside the directory and sub-directories if iDepth == null | >0
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
   * Unload then reload file
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
  
  public function getFreeFile($sName, $iDebug = 0) {
    
    $file = $this->getControler()->create('file', array(
        $this,
        $sName,
        $this->getRights(),
        $iDebug,
      ));
    
    if ($file->doExist()) return $file;
  }
  
  /**
   * Build a file, check existenz and right access
   * @param $sName The name + extension of the file
   * @param $bDebug If true, send an error message if no access is found
   * @return XML_File the file requested
   */
  public function getFile($sName, $iDebug = 0) {
    
    $this->loadRights();
    
    if ($sName && is_string($sName)) {
      
      if (!array_key_exists($sName, $this->aFiles)) {
        
        $oFile = $this->getFreeFile($sName);
        
        if ($oFile->doExist()) {
          
          if (!$aRights = $this->getSettings()->getFile($sName)) $aRights = $this->getChildrenRights();
          
          $oFile->setRights($aRights);
          
          if (\Controler::getUser()) $this->aFiles[$sName] = $oFile;
          else return $oFile;
          
        } else {
          
          $this->aFiles[$sName] = null;
          
          if ($iDebug && \FileInterface::DEBUG_EXIST) return $oFile;
        }
      }
      
      return $this->aFiles[$sName];
    }
    
    return null;
  }
  
  public function getDirectory($sName) {
    
    // if directory's rights has not yet been loaded, cause of user not yet loaded in @method __construct()'s call
    // mainly for config files and related directories rights
    $this->loadRights();
    
    if ($sName == '.') return $this;
    else if ($sName == '..') return $this->getParent();
    else if ($sName) {
      
      if (!array_key_exists($sName, $this->aDirectories)) {
        
        $oDirectory = $this->getControler()->create('directory', array(
          $this->getFullPath(),
          $sName,
          $this->getChildrenRights(),
          $this,
        ));
        
        if ($oDirectory->doExist()) $this->aDirectories[$sName] = $oDirectory;
        else $this->aDirectories[$sName] = null;
      }
      
      return $this->aDirectories[$sName];
    }
    
    return null;
  }
  
  public function getDistantFile(array $aPath, $bDebug = false) {
    
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
  
  public function checkRights($iMode) {
    
    $this->loadRights();
    
    if (!$this->isSecured() || ($iMode & $this->getUserMode())) return true;
    
    return false;
  }
  
  public function getSystemPath() {
    
    return \Controler::getSystemPath().'/'.$this->getRealPath();
  }
  
  public function getRealPath() {
    
    return $this->getParent() ? \Sylma::ROOT .$this : \Sylma::ROOT;
  }
  
  public function parseXML() {
    
    if (!$sName = xmlize($this->getName())) {
      
      $sName = t('<racine>');
      $sPath = ''; //'/';
      
    } else $sPath = $this->getFullPath();
    
    return new \XML_Element('directory', null, array(
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
    
    return new \HTML_A(\Controler::getSettings('module[@name="explorer"]/path').'?path='.$this->getFullPath(), $this->getFullPath());
  }
  
  public function __toString() {
    
    if ($this->getFullPath()) return $this->getFullPath();
    else return '';//'/';
  }
}

