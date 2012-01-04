<?php

namespace sylma\storage\fs\basic;
use \sylma\core, \sylma\dom, \sylma\storage\fs;

require_once('Resource.php');
require_once('storage/fs/directory.php');

class Directory extends Resource implements fs\directory {
  
  const NS = 'http://www.sylma.org/storage/fs/basic/directory';
  const USER_CONTROLER = 'user';
  CONST FILE_ALIAS = 'file';
  const DIRECTORY_ALIAS = 'directory';
  
  private $aDirectories = array();
  private $aFiles = array();
  private $aFreeFiles = array();
  private $settings = null;
  
  private $aChildrenRights = null;
  
  public function __construct($sName, fs\directory $parent = null, array $aRights = array(), fs\controler $controler = null) {
    
    $this->sFullPath = $parent ? $parent. '/' .$sName : '';
    $this->controler = $controler;
    $this->parent = $parent;
    $this->sName = $sName;
    
    $this->bExist = is_dir($this->getRealPath());
    
    if ($this->doExist()) {
      
      $this->aRights = $this->aChildrenRights = $aRights;
      
      $settings = $this->getControler()->create('security', array($this, $this->getControler()));
      $this->setSettings($settings);
      
      $this->loadRights(); //if ($parent) 
    }
  }
  
  private function getChildrenRights() {
    
    return $this->aChildrenRights;
  }
  
  protected function setSettings(fs\security\manager $settings) {
    
    $this->settings = $settings;
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
    
    if (!$this->isSecured() && $this->getSettings()->isReady()) {
      
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
    
    if ($excluded = $this->getControler()->getArgument('browse/excluded')) {
      
      $aPaths += $excluded->query();
    }
    
    if ($iDepth === null || $iDepth > 0) {
      
      if ($iDepth) $iDepth--;
      
      $aFiles = scandir($this->getRealPath(), 0);
      
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
          }
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
    
    if (array_key_exists($sAlias, $this->aFiles) && array_key_exists($sName, $this->aFiles[$sAlias])) unset($this->aFiles[$sAlias][$sName]);
    return $this->getFile($sName);
  }
  
  /**
   * Unload then reload directory
   */
  public function updateDirectory($sName) {
    
    $sAlias = $this->getAlias(self::FILE_ALIAS);
    
    if (array_key_exists($sAlias, $this->aDirectories) && array_key_exists($sName, $this->aDirectories[$sAlias])) unset($this->aDirectories[$sAlias][$sName]);
    return $this->getDirectory($sName);
  }
  
  protected function getAlias($sClass) {
    
    $fs = $this->getControler();
    
    if ($sMode = $fs->getMode()) $sClass .= '/' . $sMode;
    
    return $sClass;
  }
  
  public function getFreeFile($sName, $iDebug = self::DEBUG_LOG) {
    
    $result = null;
    $sAlias = $this->getAlias(self::FILE_ALIAS);
    
    if (array_key_exists($sAlias, $this->aFiles) && array_key_exists($sName, $this->aFiles[$sAlias])) {
      
      $result = $this->aFiles[$sAlias][$sName];
    }
    else {
      
      $result = $this->loadFreeFile($sName, $iDebug);
    }
    
    return $result;
  }
  
  protected function loadFreeFile($sName, $iDebug) {
    
    $result = null;
    $sClass = $this->getAlias(self::FILE_ALIAS);
    
    $file = $this->getControler()->create($sClass, array(
        $sName,
        $this,
        $this->getRights(),
        $iDebug,
      ));
    
    if ($file->doExist() || $iDebug & self::DEBUG_EXIST) {
      
      $result = $file;
    }
    
    if (!$file->doExist() && $iDebug & self::DEBUG_LOG) {
      
      $this->throwException(t('File does not exists'));
    }
    
    if (!array_key_exists($sClass, $this->aFiles)) $this->aFiles[$sClass] = array();
    
    $this->aFiles[$sClass][$sName] = $result;
    
    return $result;
  }
  
  /**
   * Build a file, check existenz and right access
   * If @controler user is not set, then file is returned without rights check but not cached
   * 
   * @param $sName The name + extension of the file
   * @param $iDebug send an error message if no access is found see @class fs\directory
   * @return null|fs\file the file requested
   */
  public function getFile($sName, $iDebug = self::DEBUG_LOG) {
    
    $result = null;
    $this->loadRights();
    
    if (!$this->isSecured()) {
      
      $this->throwException(txt('Unauthorized access to @file %s', $this . '/' . $sName));
    }
    
    if ($sName && is_string($sName)) {
      
      $sClass = $this->getAlias(self::FILE_ALIAS);
      
      if (array_key_exists($sClass, $this->aFiles) && array_key_exists($sName, $this->aFiles[$sClass])) {
        
        // yet builded
        $file = $this->aFiles[$sClass][$sName];
        
        if (!$file->isSecured()) {
          
          $this->secureFile($file);
        }
        
        $result = $file;
      }
      else {
        
        // not yet builded, build it
        $file = $this->loadFreeFile($sName, $iDebug);
        
        if ($file) {
          
          $this->secureFile($file);
          $result = $file;
        }
        else {
          
          if ($iDebug & self::DEBUG_EXIST) $result = $file;
        }
      }
    }
    
    return $result;
  }
  
  protected function secureFile(fs\file $file) {
    
    //dspf($file);
    
    if (!$aRights = $this->getSettings()->getFile($file->getName())) $aRights = $this->getChildrenRights();
    
    $file->setRights($aRights);
    $file->isSecured(true);
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
      
      $sAlias = $this->getAlias(self::DIRECTORY_ALIAS);
      
      if (array_key_exists($sAlias, $this->aDirectories) && array_key_exists($sName, $this->aDirectories[$sAlias])) {
        
        // yet builded
        $result = $this->aDirectories[$sAlias][$sName];
      }
      else {
        
        // not yet builded, build it
        $dir = $this->getControler()->create($sAlias, array(
          $sName,
          $this,
          $this->getChildrenRights(),
        ));
        
        if ($dir->doExist()) $result = $this->aDirectories[$sAlias][$sName] = $dir;
        else $this->aDirectories[$sAlias][$sName] = null;
      }
    }
    
    return $result;
  }
  
  public function getDistantFile(array $aPath, $iDebug = self::DEBUG_LOG) {
    
    $result = null;
    
    if ($aPath) {
      
      if (count($aPath) == 1) {
        
        return $this->getFile($aPath[0], $iDebug);
        
      } else {
        
        $sName = array_shift($aPath);
        
        $dir = $this->getDirectory($sName);
        
        if (!$dir && $iDebug & self::DEBUG_LOG) {
          
          $this->throwException(txt('Directory %s does not exists', $sName));
        }
        
        $result = $dir->getDistantFile($aPath, $iDebug);
      }
    }
    
    return $result;
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
    
    return ($this->getParent() ? $this->getParent()->getRealPath() . '/' . $this->getName() : \Sylma::ROOT . $this->getName());
  }
  
  public function asToken() {
    
    return '@directory ' . (string) $this;
  }
  
  public function asArgument() {
    
    if (!$this->getParent()) {
      
      $sName = t('<racine>');
      $sPath = '';
      
    } else {
      
      $sName = $this->getName();
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
  
  public function __toString() {
    
    return $this->getFullPath();
  }
}

