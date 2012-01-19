<?php

namespace sylma\core\module\test\samples;
use sylma\core;

require_once('core/module/Domed.php');

class Domed extends core\module\Domed {
  
  public function __construct() {
    
    $this->setDirectory(__file__);
  }

  public function createArgument($mArguments, $sNamespace = '') {
    
    return parent::createArgument($mArguments, $sNamespace);
  }
  
  public function getDocument($sPath, $iMode = \Sylma::MODE_READ) {
    
    return parent::getDocument($sPath, $iMode);
  }
}