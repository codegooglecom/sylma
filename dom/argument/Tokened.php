<?php

namespace sylma\dom\argument;
use sylma\core, sylma\dom;

require_once('Iterator.php');

class Tokened extends Iterator {
  
  protected $aTokens = array();
  
  public function parseName($sName) {
    
    $sResult = '';
    
    if ($sName{0} == '#') {
      
      if (!$iToken = strpos($sName, ':')) {
        
        $this->throwException(sprintf('Invalid token : %s', $sName));
      }
      
      $sToken = substr($sName, 1, $iToken - 1);
      $sContent = substr($sName, $iToken + 1);
      
      $aToken = $this->getToken($sToken);
      
      $sResult = $aToken['element'] . '[@' . $aToken['attribute'] . ' = \'' . $sContent . '\']';
      $sResult = $this->parseName($sResult);
    }
    else {
      
      $sResult = parent::parseName($sName);
    }
    
    return $sResult;
  }
  
  public function registerToken($sName, $sElement, $sAttribute) {
    
    $this->aTokens[$sName] = array(
      'element' => $sElement,
      'attribute' => $sAttribute,
    );
  }
  
  protected function getToken($sName) {
    
    if (!array_key_exists($sName, $this->aTokens)) {
      
      $this->throwException(sprintf('Unkown token : %s', $sName));
    }
    
    return $this->aTokens[$sName];
  }
}
