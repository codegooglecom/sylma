<?php

namespace sylma\core\argument\test\controler;
use \sylma\core\argument\test, \sylma\core\module, \sylma\storage\fs;

require_once('core/argument/test/controler.php');

class Filed extends module\Filed implements test\controler {
  
  public function setDirectory($mDirectory) {
    
    parent::setDirectory($mDirectory);
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