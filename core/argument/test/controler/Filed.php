<?php

namespace sylma\core\argument\test\controler;
use \sylma\core\argument\test, \sylma\core, \sylma\storage\fs;

require_once('core/module/Filed.php');
require_once('core/argument/test/controler.php');

class Filed extends core\module\Filed implements test\controler {
  
  public function __construct(fs\directory $dir) {
    
    $this->setDirectory($dir);
  }
  
  public function createArgument($mArguments = array(), $sNamespace = '') {
    
    if (is_string($mArguments)) {
      
      $mArguments = (string) $this->getFile($mArguments);
    }
    
    if ($sNamespace) $aNS = array($sNamespace);
    else $aNS = array();
    
    return $this->create($this->readArgument('class-alias'), array($mArguments, $aNS));
  }
  
  public function getDirectory($sPath = '', $bDebug = true) {
    
    return parent::getDirectory();
  }
  
  public function getNamespace($sPrefix = null) {
    
    return parent::getNamespace($sPrefix);
  }

  public function setArguments($mArguments = null, $bMerge = true) {
    
    parent::setArguments($mArguments, $bMerge);
  }
  
  public function getArguments() {
    
    return parent::getArguments();
  }
  
  public function get($sPath) {
    
    return $this->getArguments()->get($sPath);
  }
  
  public function set($sPath, $mVar) {
    
    return $this->getArguments()->set($sPath, $mVar);
  }
}