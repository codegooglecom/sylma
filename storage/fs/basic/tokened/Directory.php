<?php

namespace sylma\storage\fs\basic\tokened;
use \sylma\storage\fs;

require_once(dirname(__dir__) . '/Directory.php');
require_once(dirname(dirname(__dir__)) . '/tokened/directory.php');

class Directory extends fs\basic\Directory implements fs\tokened\directory {
  
  protected $aTokens = array();
  protected $bPropagate = false;
  
  public function registerToken($sName, $sValue, $bPropagate = false) {
    
    $this->aTokens[$sName] = array(
          'value' => $sValue,
          'propagate' => $bPropagate,
    );
    
    if ($bPropagate) $this->bPropagate = true;
  }
  
  protected function doPropagate() {
    
    return $this->bPropagate;
  }
  
  protected function loadDirectory($sName, $iDebug) {
    
    $result = parent::loadDirectory($sName, $iDebug);
    
    if ($this->doPropagate() && $result) {
      
      foreach($this->aTokens as $sName => $aToken) {
        
        if ($aToken['propagate']) {
          
          $result->registerToken($sName, $aToken['value'], true);
        }
      }
    }
    
    return $result;
  }

  
  public function getDirectory($sName, $iDebug = self::DEBUG_LOG) {
    
    if ($sName{0} == '#') {
      
      $sName = $this->getToken($sName);
    }
    
    return parent::getDirectory($sName, $iDebug);
  }
  
  protected function getToken($sName) {
    
    if (!array_key_exists($sName, $this->aTokens)) {
      
      $this->throwException(txt('Token %s does not exists', $sName));
    }
    
    return $this->aTokens[$sName]['value'];
  }
}

