<?php

namespace sylma\storage\fs\basic;
use \sylma\storage\fs;

require_once('Resource.php');
require_once('storage/fs/file.php');

class File extends Resource implements fs\file {
  
  const NS = 'http://www.sylma.org/storage/fs/basic/file';
  const DOM_CONTROLER = 'dom';
  
  /**
   * @var string
   */
  private $sExtension = '';
  
  /**
   * Size of the file in octets(o)
   * @var integer
   */
  private $iSize = null;
  
  /**
   * Date of last changed in unix timestamp
   * @var integer
   */
  private $iChanged = null;
  
  private $oSettings = null;
  
  private $bFileSecured = false;
  
  public function __construct($sName, fs\directory $parent, array $aRights, $iDebug) {
    
    $this->sFullPath = (string) $parent . '/' . $sName;
    $this->sName = $sName;
    $this->parent = $parent;
    
    $this->bExist = is_file($this->getRealPath());
    
    if ($this->doExist() || $iDebug & self::DEBUG_EXIST) {
      
      $this->aRights = $aRights;
      
      if ($iExtension = strrpos($sName, '.')) $this->sExtension = substr($sName, $iExtension + 1);
      else $this->sExtension = '';
    }
    else if ($iDebug & self::DEBUG_LOG) {
      
      $this->throwException(txt('@file %s does not exist', $this->getRealPath()), array(), 16); // todo too depth !
    }
  }
  
  public function getLastChange() {
    
    if ($this->iChanged === null && $this->doExist()) $this->iChanged = filemtime($this->getRealPath());
    
    return $this->iChanged;
  }
  
  // public function getActionPath() {
    
    // $sPath = substr($this->getFullPath(), 0, strlen($this->getFullPath()) - strlen($this->getExtension()) - 1);
    // return $this->getName() == 'index.eml' ? substr($sPath, 0, -6) : $sPath;
  // }
  
  // public function getSimpleName() {
    
    // return substr($this->getName(), 0, strlen($this->getName()) - strlen($this->getExtension()) - 1);
  // }
  
  // public function getDisplayName() {
    
    // return str_replace('_', ' ', substr($this->getName(), 0, strlen($this->getName()) - strlen($this->getExtension()) - 1));
  // }
  
  public function getExtension() {
    
    return $this->sExtension;
  }
  
  public function getSize() {
    
    if ($this->iSize === null && $this->doExist()) $this->iSize = filesize($this->getRealPath());
    
    return $this->iSize;
  }
  
  public function getSystemPath() {
    
    return $this->getParent()->getSystemPath().'/'.$this->getName();
  }
  
  public function getRealPath() {
    
    return $this->getParent()->getRealPath().'/'.$this->getName();
  }
  
  public function isLoaded() {
    
    return (bool) $this->oDocument;
  }
  
  public function getSimpleName() {
    
    return substr($this->getName(), 0, strlen($this->getName()) - strlen($this->getExtension()) - 1);
  }
  
  /**
   * Get a copy of the corresponding document
   * @param integer $iMode : The mode used to load the document
   */
  public function getFreeDocument() {
    
    $result = null;
    
    if (!$this->getControler()) { // todo, usefull ?
      
      \Sylma::throwException(t('File controler is not ready'), array(), 0);
    }
    
    $dom = $this->getControler('dom');
    //if ($dom = \Sylma::getControler(self::DOM_CONTROLER, false, false)) {
      
      $result = $this->getControler()->create('file/document');

      $result->setFile($this);
      $result->loadFile();
    //}
    
    return $result;
  }
  
  public function getDocument($iMode = \Sylma::MODE_READ) {
    
    return $this->getFreeDocument();
  }
  
  public function checkRights($iMode) {
    
    if (!$this->isSecured() || ($iMode & $this->getUserMode())) return true;
    
    return false;
  }
  
  public function getSettings($bRecursive = false) {
    
    return $this->getParent()->getSettings($bRecursive);
  }
  
  public function readArray() {
    
    return file($this->getRealPath(), FILE_SKIP_EMPTY_LINES);
  }
  
  public function read() {
    
    return file_get_contents($this->getRealPath());
  }
  
  public function asToken() {
    
    return '@file ' . (string) $this;
  }
  
  public function asArgument() {
    
    $iSize = ($this->getSize() / 1000);
    
    if ($iSize < 1) $iSize = 1;
    
    return $this->getControler()->createArgument(array(
      'file' => array(
        'full-path' => $this->getFullPath(),
        'name' => $this->getName(),
        'simple-name' => $this->getSimpleName(),
        //'display-name' => $this->getDisplayName(),
        'owner' => $this->getOwner(),
        'group' => $this->getGroup(),
        'mode' => $this->getMode(),
        'read' => booltostr($this->checkRights(MODE_READ)),
        'write' => booltostr($this->checkRights(MODE_WRITE)),
        'execution' => booltostr($this->checkRights(MODE_EXECUTION)),
        'size' => $iSize,
        'extension' => $this->getExtension(),
      ),
    ), self::NS);
  }
}

