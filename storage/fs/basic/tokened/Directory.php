<?php

namespace sylma\storage\fs\basic\tokened;
use \sylma\storage\fs;

require_once(dirname(__dir__) . '/Directory.php');
require_once(dirname(dirname(__dir__)) . '/tokened/directory.php');

class Directory extends fs\basic\Directory implements fs\tokened\directory {
  
  protected $aTokens = array();
  protected $bTokens = false;
  
  public function registerToken($sName, $sValue, $bPropagate = false) {
    
    $this->aTokens[$sName] = array(
          'value' => $sValue,
          'propagate' => $bPropagate,
    );
    
    if ($bPropagate) $this->bTokens = true;
  }
  
  protected function propagateToken() {
    
    return $this->bTokens;
  }
  
  protected function loadDirectory($sName, $iDebug) {
    
    $result = parent::loadDirectory($sName, $iDebug);
    
    if ($this->propagateToken() && $result) {
      
      foreach($this->aTokens as $sName => $aToken) {
        
        if ($aToken['propagate']) {
          
          $result->registerToken($sName, $aToken['value'], true);
        }
      }
    }
    
    return $result;
  }

  protected function parseName($sName) {
    
    if ($sName{0} == '#') {
      
      $sName = $this->getToken($sName);
    }
    
    return $sName;
  }
  
  public function getDirectory($sName, $iDebug = self::DEBUG_LOG) {
    
    if ($sName) $sName = $this->parseName($sName);
    
    return parent::getDirectory($sName, $iDebug);
  }
  
  protected function getToken($sName) {
    
    $sName = substr($sName, 1);
    
    if (!array_key_exists($sName, $this->aTokens)) {
      
      $this->throwException(txt('Token #%s does not exists', $sName));
    }
    
    return $this->aTokens[$sName]['value'];
  }
}

