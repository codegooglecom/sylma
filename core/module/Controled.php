<?php

namespace sylma\core\module;
use \sylma\core;

require_once('core/controled.php');
require_once('Exceptionable.php');

abstract class Controled extends Exceptionable implements core\controled {
  
  protected $controler;
  protected $aControlers = array();
  
  public function setControler(core\factory $controler, $sName = '') {
    
    if ($controler === $this) {
      
      $this->throwException(t('Cannot use controler as himself'));
    }
    
    $this->controler = $controler;
  }
  
  public function getControler($sName = '', $bDebug = true) {
    
    $result = null;
    
    if ($sName) {
      
      $result = $this->loadControler($sName);
    }
    else {
      
      if ($bDebug && !$this->controler) {
        
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
  
  public function getNamespace($sPrefix = null) {
    
    $sNamespace = parent::getNamespace();
    
    if (!$sNamespace && $this->getControler('', false)) {
      
      $sNamespace = $this->getControler()->getNamespace();
    }
    
    return $sNamespace;
  }

}