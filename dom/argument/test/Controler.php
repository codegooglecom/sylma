<?php

namespace sylma\dom\argument\test;
use sylma\core;

require_once('core/argument/test/controler/Filed.php');

class Controler extends core\argument\test\controler\Filed {
  
  public function createArgument($mArguments = array(), $sNamespace = '') {
    
    $file = $this->getFile($mArguments);
    
    return parent::createArgument($file, $sNamespace);
  }
}
