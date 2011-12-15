<?php

namespace sylma\storage\fs;
use \sylma\core, \sylma\storage\fs, \sylma\core\functions\path;

require_once('core/module/Filed.php');
require_once('core/functions/Path.php');

class Controler extends core\module\Argumented {
  
  const NS = 'http://www.sylma.org/storage/fs/controler';
  const SETTINGS = 'settings.yml';
  
  private $directory;
  private $bEditable = false;
  private $sMode = '';
  
  public function __construct($sPath = '') {
    
    $sDirectory = $this->extractDirectory(__file__, false);
    $this->setArguments(new core\argument\Filed(path\toAbsolute(self::SETTINGS, $sDirectory)));
    
    $this->directory = $this->create('directory', array($sPath, null, $this->getArgument('rights')->query(), $this));
    
    $this->setNamespace(self::NS);
  }
  
  public function create($sName, array $aArguments = array(), $sDirectory = '') {
    
    return parent::create($sName, $aArguments, $sDirectory);
  }
  
  public function setMode($sName) {
    
    $this->sMode = $sName;
  }
  
  public function getMode() {
    
    return $this->sMode;
  }
  
  public function extractDirectory($sPath, $bObject = true) {
    
    $sPath = substr($sPath, strlen(getcwd() . \Sylma::ROOT) + 1);
    if (SYLMA_XAMPP_BUG && \Sylma::isWindows()) $sPath = str_replace('\\', '/', $sPath);
    else if (preg_match("/Win/", getenv("HTTP_USER_AGENT" ))) $sPath = str_replace('\\', '/', $sPath);
    
    $sResult = substr($sPath, 0, strlen($sPath) - strlen(strrchr($sPath, '/')));
    
    if ($bObject) return $this->getDirectory($sResult);
    else return $sResult;
  }
  
  public function getArgument($sPath, $mDefault = null, $bDebug = false) {
    
    return parent::getArgument($sPath, $mDefault, $bDebug);
  }
  
  public function getDirectory($sPath = '') {
    
    if ($sPath && $sPath != '/') {
      
      $aPath = explode('/', $sPath);
      array_shift($aPath);
      
      return $this->directory->getDistantDirectory($aPath);
    }
    else {
      
      return $this->directory;
    }
  }
  
  public function getFile($sPath, $mSource = null, $bDebug = false) {
    
    $sPath = path\toAbsolute($sPath, $mSource);
    
    $aPath = explode('/', $sPath);
    array_shift($aPath);
    
    return $this->getDirectory()->getDistantFile($aPath, $bDebug);
  }
  
  protected function getUnsafes() {
    
    $aResult = array();
    
    if ($unsafes = $this->getArgument('unsafes')) {
      
      $aResult = $unsafes->query();
    }
    
    return $aResult;
  }
  
  public function checkUnsafe(fs\file $file) {
    dspm((string) $file);
    if (!in_array((string) $file, $this->getUnsafes())) {
      
      $this->throwException(txt('%s should be secured', $file->asToken()));
    }
    
    return true;
  }
  
  public function throwException($sMessage, $mSender = array(), $iOffset = 1) {
    
    $mSender = (array) $mSender;
    $mSender[] = '@namespace ' . $this->getNamespace();
    
    \Sylma::throwException($sMessage, $mSender, $iOffset);
  }
}