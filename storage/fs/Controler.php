<?php

namespace sylma\storage\fs;
use \sylma\core;

require_once('core/module/Filed.php');

class Controler extends core\module\Argumented {
  
  const NS = 'http://www.sylma.org/storage/fs/controler';
  const SETTINGS = 'settings.yml';
  
  protected $directory;
  
  public function __construct($sPath = '') {
    
    $sDirectory = $this->extractDirectory(__file__, false);
    $this->setArguments(new core\argument\Filed(path_absolute(self::SETTINGS, $sDirectory)));
    
    $this->directory = $this->create('directory', array($sPath, null, $this->getArgument('rights')->query(), $this));
    
    $this->setNamespace(self::NS);
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
    
    $sPath = path_absolute($sPath, $mSource);
    
    $aPath = explode('/', $sPath);
    array_shift($aPath);
    
    return $this->getDirectory()->getDistantFile($aPath, $bDebug);
  }
  
  public function throwException($sMessage, $mSender = array(), $iOffset = 1) {
    
    $mSender = (array) $mSender;
    $mSender[] = '@namespace ' . $this->getNamespace();
    
    \Sylma::throwException($sMessage, $mSender, $iOffset);
  }
}