<?php

namespace sylma\parser\action\php\basic;
use \sylma\core;

require_once('core/argumentable.php');
require_once('core/module/Argumented.php');

abstract class SimpleVar extends core\module\Argumented implements core\argumentable {
  
  private $sName = '';
  
  protected function setName($sName) {
    
    $this->sName = $sName;
  }
  
  protected function getName() {
    
    return $this->sName;
  }
  
  public function asArgument() {
    
    return $this->createArgument(array(
      'var' => array(
        '@name' => $this->sName,
      ),
    ));
  }
}