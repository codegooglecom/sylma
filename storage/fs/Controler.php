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
  
  protected static $argumentClass = 'sylma\core\argument\Filed';
  
  public function __construct($sPath = '', $bEditable = false, $bFS = true) {
    
    $this->setNamespace(self::NS);
    
    $sDirectory = $this->extractDirectory(__file__, false);
    
    if (!$bFS) $sDirectory = \Sylma::ROOT . $sDirectory;
    
    $arg = $this->createArgument(path\toAbsolute(self::SETTINGS, $sDirectory));
    $this->setArguments($arg);
    
    if ($bEditable) $this->setEditable();
  }
  
  protected function setEditable() {
    
    $this->setArgument('classes/file/name', $this->readArgument('classes/file/classes/editable/name'));
    $this->setArgument('classes/directory/name', $this->readArgument('classes/directory/classes/editable/name'));
  }
  
  public function loadDirectory($sPath = '') {
    
    $dir = $this->create('directory', array($sPath, null, $this->getArgument('rights')->query(), $this));
    
    if ($tokens = $this->getArgument('tokens')) {
      
      foreach ($tokens as $sName => $token) {
        
        $dir->registerToken($sName, $token->read('path'), $token->read('propagate', false));
      }
    }
    
    $this->setDirectory($dir);
  }
  
  public function setDirectory(fs\directory $dir) {
    
    $this->directory = $dir;
  }
  
  public function create($sName, array $aArguments = array(), $sDirectory = '') {
    
    return parent::create($sName, $aArguments, $sDirectory);
  }
  
  public function createArgument($mArguments, $sNamespace = '') {
    
    return parent::createArgument($mArguments, $sNamespace);
  }
  
  public function extractDirectory($sFile, $bObject = true) {
    
    $sFile = substr($sFile, strlen(getcwd() . \Sylma::ROOT) + 1);
    if (\Sylma::isWindows()) $sFile = str_replace('\\', '/', $sFile);
    
    $sResult = substr($sFile, 0, strlen($sFile) - strlen(strrchr($sFile, '/')));
    
    if ($bObject) return $this->getDirectory($sResult);
    else return $sResult;
  }
  
  public function readArgument($sPath, $mDefault = null, $bDebug = false) {
    
    return parent::readArgument($sPath, $mDefault, $bDebug);
  }
  
  public function getArgument($sPath, $mDefault = null, $bDebug = false) {
    
    return parent::getArgument($sPath, $mDefault, $bDebug);
  }
  
  public function getDirectory($sPath = '', $bDebug = true) {
    
    if ($sPath && $sPath != '/') {
      
      // for relative path use, else @function explode() return empty
      if ($sPath{0} != '/') $sPath = '/' . $sPath;
      
      $aPath = explode('/', $sPath);
      array_shift($aPath);
      
      $iDebug = 0;
      if ($bDebug) $iDebug = basic\Resource::DEBUG_LOG;
      
      return $this->directory->getDistantDirectory($aPath, $iDebug);
    }
    else {
      
      return $this->directory;
    }
  }
  
  public function getFile($sPath, $mSource = null, $bDebug = true) {
    
    $sPath = path\toAbsolute($sPath, $mSource);
    
    $aPath = explode('/', $sPath);
    array_shift($aPath);
    
    require_once('basic/Resource.php');
    
    $iDebug = 0;
    if ($bDebug) $iDebug = basic\Resource::DEBUG_LOG;
    
    return $this->getDirectory()->getDistantFile($aPath, $iDebug);
  }
  
  public function setArgument($sPath, $mValue) {
    
    return parent::setArgument($sPath, $mValue);
  }
  
  public function getArguments() {
    return parent::getArguments();
  }

  public function throwException($sMessage, $mSender = array(), $iOffset = 1) {
    
    $mSender = (array) $mSender;
    $mSender[] = '@namespace ' . $this->getNamespace();
    
    \Sylma::throwException($sMessage, $mSender, $iOffset);
  }
}