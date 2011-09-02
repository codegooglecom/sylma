<?php

namespace sylma\storage\fs\basic;
use \sylma\core, \sylma\dom, \sylma\storage\fs;

require_once('Resource.php');
require_once('storage/fs/directory.php');

class Directory extends Resource implements fs\directory {
  
  const NS = 'http://www.sylma.org/storage/fs/basic/directory';
  
  private $aDirectories = array();
  private $aFiles = array();
  private $aFreeFiles = array();
  private $settings = null;
  
  private $aChildrenRights = null;
  
  public function __construct($sName, fs\directory $parent = null, array $aRights = array(), fs\controler $controler = null) {
    
    $this->sFullPath = $parent ? $parent. '/' .$sName : $sName;
    $this->controler = $controler;
    $this->parent = $parent;
    
    if (is_dir($this->getRealPath())) {
      
      $this->aRights = $this->aChildrenRights = $aRights;
      $this->sName = $sName;
      $this->sPath = (string) $parent;
      
      $this->doExist(true);
      $this->loadRights(); //if ($parent) 
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
      else $this->throwException(t('No security file in parent directory'));
    }
    
    return $this->settings;
  }
  
  private function loadRights() {
    
    if (!$this->isSecured() && \Sylma::getControler('user', false)) {
      
      $this->settings = $this->getControler()->create('security', array($this, $this->getControler()));
      
      // self rights
      $aRights = $this->setRights($this->getSettings()->getDirectory());
      
      // children rights
      if ($aChildrenRights = $this->getSettings()->getPropagation()) {
        
        $this->aChildrenRights = $aChildrenRights;
        
      } else {
        
        $this->aChildrenRights = $aRights;
      }
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
  
  public function browse(array $aExtensions, array $aPaths = array(), $iDepth = null, $bRender = true) {
    
    $result = $this->parse();
    $aPaths += $this->getControler()->getArgument('browse/excluded')->query();
    
    // if ($this->getName() == '.svn') dspm('pas beau', 'error');
    if ($iDepth === null || $iDepth > 0) {
      
      if ($iDepth) $iDepth--;
      
      $aFiles = scandir(\Sylma::ROOT . $this->getFullPath(), 0);
      
      foreach ($aFiles as $sFile) {
        
        if ($sFile != '.' && $sFile != '..') {
          
          if ($file = $this->getFile($sFile)) {
            
            if ($file->getUserMode() != 0 && $bRender &&
              (!$aExtensions || in_array(strtolower($file->getExtension()), $aExtensions))) {
              
              $result->add('#file', $file->parse()->get('file'));
            }
          }
          else if ($dir = $this->getDirectory($sFile)) {
            
            $bValid = true;
            
            foreach ($aPaths as $sPath) {
              
              switch ($sPath{0}) {
                
                case '/' : if ($sPath == $dir->getFullPath()) $bValid = false; break;
                default : if ($sPath == $dir->getName()) $bValid = false; break;
              }
            }
            
            if ($bValid && $bRender) {
              
              $result->add('#directory', $dir->browse($aExtensions, $aPaths, $iDepth)->get('directory'));
            }
          } else dspm('RIEN');
        }
      }
    }
    
    return $result;
  }
  
  /*
   * Browse then return list of files inside the directory and sub-directories if iDepth == null | >0
   */
  public function getFiles(array $aExtensions = array(), $sPreg = null, $iDepth = 0) {
    
    $this->browse($aExtensions, array(), 1, false);
    $aResult = array();
    
    // Files of current directory
    
    if ($aExtensions) {
      
      foreach ($this->aFiles as $sFile => $file) {
        
        if ($file) {
          
          $bExtension = !$aExtensions || in_array(strtolower($file->getExtension()), $aExtensions);
          $bPreg = !$sPreg || preg_match($sPreg, $sFile);
        
          if ($bExtension && $bPreg) $aResult[] = $file;
        }
      }
      
    } else $aResult = array_values($this->aFiles);
    
    // Recursion in sub-directory
    
    if ($iDepth === null || $iDepth > 0) {
      
      if ($iDepth) $iDepth--;
      
      foreach ($this->aDirectories as $dir) {
        
        if ($dir) $aResult = array_merge($aResult, $dir->getFiles($aExtensions, $sPreg, $iDepth));
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
        $sName,
        $this,
        $this->getRights(),
        $iDebug,
      ));
    
    if ($file->doExist()) return $file;
  }
  
  /**
   * Build a file, check existenz and right access
   * If @controler user is not set, then file is returned without rights check but not cached
   * 
   * @param $sName The name + extension of the file
   * @param $bDebug If true, send an error message if no access is found
   * @return null|fs\file the file requested
   */
  public function getFile($sName, $iDebug = 0) {
    
    $result = null;
    $this->loadRights();
    
    if ($sName && is_string($sName)) {
      
      if (array_key_exists($sName, $this->aFiles)) {
        
        // yet builded
        $result = $this->aFiles[$sName];
      }
      else {
        
        // not yet builded, build it
        $file = $this->getFreeFile($sName);
        
        if ($file && $file->doExist()) {
          
          if (!$aRights = $this->getSettings()->getFile($sName)) $aRights = $this->getChildrenRights();
          
          $file->setRights($aRights);
          
          if (\Sylma::getControler('user')) $this->aFiles[$sName] = $file;
          
          $result = $file;
        }
        else {
          
          $this->aFiles[$sName] = null;
          if ($iDebug && fs\file::DEBUG_EXIST) $result = $file;
        }
      }
    }
    
    return $result;
  }
  
  public function getDirectory($sName) {
    
    $result = null;
    
    // Mainly for config files and related directories rights for wich security rights
    // has not yet been loaded in @method __construct() cause of missing @controler user
    $this->loadRights();
    
    if ($sName == '.') {
      
      $result = $this;
    }
    else if ($sName == '..') {
      
      $result = $this->getParent();
    }
    else if ($sName) {
      
      if (array_key_exists($sName, $this->aDirectories)) {
        
        // yet builded
        $result = $this->aDirectories[$sName];
      }
      else {
        
        // not yet builded, build it
        $dir = $this->getControler()->create('directory', array(
          $sName,
          $this,
          $this->getChildrenRights(),
        ));
        
        if ($dir->doExist()) $result = $this->aDirectories[$sName] = $dir;
        else $this->aDirectories[$sName] = null;
      }
    }
    
    return $result;
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
    
    return \Sylma::ROOT . $this;
  }
  
  public function parse() {
    
    if (!$sName = $this->getName()) {
      
      $sName = t('<racine>');
      $sPath = '';
      
    } else {
      
      $sPath = $this->getFullPath();
    }
    
    return $this->getControler()->createArgument(array(
      'directory' => array(
        'full-path' => $sPath,
        'owner' => $this->getOwner(),
        'group' => $this->getGroup(),
        'mode' => $this->getMode(),
        'read' => booltostr($this->checkRights(\Sylma::MODE_READ)),
        'write' => booltostr($this->checkRights(\Sylma::MODE_WRITE)),
        'execution' => booltostr($this->checkRights(\Sylma::MODE_EXECUTE)),
        'name' => $sName,
      ),
    ), self::NS);
  }
  
  public function parseXML() {
    
    return $this->getFragment($this->asArray());
  }
  
  public function __toString() {
    
    return $this->getFullPath();
  }
}

