<?php

namespace sylma\core\module;
use \sylma\core;

require_once('core/controled.php');
require_once('Exceptionable.php');

abstract class Controled extends Exceptionable implements core\controled {
  
  protected $controler;
  protected $aControlers = array();
  
  public function setControler(core\factory $controler, $sName = '') {
    
    $this->controler = $controler;
  }
  
  public function getControler($sName = '') {
    
    $result = null;
    
    if ($sName) {
      
      $result = $this->loadControler($sName);
    }
    else {
      
      if (!$this->controler) {
        
        $this->throwException('No controler defined');
      }
      
      $result = $this->controler;
    }
    
    return $result;
  }
  
  protected function loadControler($sName) {
    
    $controler = null;
    
    if (array_key_exists($sName, $this->aControlers)) {
      
      $controler = $this->aControlers[$sName];
    }
    else {
      
      $controler = \Sylma::getControler($sName);
    }
    
    return $controler;
  }
}