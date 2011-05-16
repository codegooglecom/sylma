<?php

class Arguments implements ArgumentsInterface {
  
  const MESSAGES_STATUT = Sylma::LOG_STATUT_DEFAULT;
  private $aArray = array();
  private $sNamespace = '';
  
  public function __construct(array $aArray = array(), $sNamespace = '') {
    
    $this->aArray = $aArray;
    $this->sNamespace = $sNamespace;
  }
  
  public function getNamespace() {
    
    return $this->sNamespace;
  }
  
  public function set($sPath, $mValue = null) {
    
    if ($aTarget = $this->get($sPath)) {
      
      if ($mValue !== null) $aTarget = $mValue;
      else unset($aTarget);
    }
    
    return $aTarget;
  }
  
  public function get($sPath, $bDebug = true) {
    
    if (!$sPath) $this->log('Aucun chemin indiqué dans la requête', 'warning');
    else {
      
      if ($sPath[0] == '/') $sPath = substr($sPath, 1);
      
      if (strpos($sPath, '/') !== false) $aPath = explode('/', $sPath);
      else $aPath = array($sPath);
      
      return $this->getValue($aPath, $this->aArray, $bDebug, $sPath);
    }
    
    return null;
  }
  
  private function getValue(array $aPath, array $aArray, $bDebug, $sPath) {
    
    $mResult = null;
    $sKey = array_shift($aPath);
    
    if (!array_key_exists($sKey, $aArray)) {
      
      if ($bDebug) $this->log("Cannot find '$sPath', stopped at key '$sKey'");
      
      $mResult = null;
    }
    else {
      
      if (!$aPath) $mResult = $aArray[$sKey];
      else if (is_array($aArray[$sKey])) $mResult = $this->getValue($aPath, $aArray[$sKey], $bDebug, $sPath);
      else $this->log("Aucun sous-chemin dans $sPath");
    }
    
    return $mResult;
  }
  
  public function read($sPath, $bDebug = true) {
    
    $sResult = $this->get($sPath, $bDebug);
    
    if (is_array($sResult)) {
      
      $this->log("Cannot read array in $sPath");
      $sResult = '';
    }
    
    return $sResult;
  }
  
  public function merge(array $aArray) {
    
    $this->aArray = $this->mergeArrays($this->aArray, $aArray);
  }
  
  private function mergeArrays(array $array1, array $array2) {
    
    foreach($array2 as $key => $val) {
      
      if(array_key_exists($key, $array1) && is_array($val)) {
        
        $array1[$key] = $this->mergeArrays($array1[$key], $array2[$key]);
      }
      else {
        
        $array1[$key] = $val;
      }
    }
    
    return $array1;
  }
  
  /**
   * Build a Options object with his own arguments
   * @param XML_Element $oRoot The root node to insert the results to
   * @param? XML_Document $oSchema The schema that will be used by the Options object
   * @param? string $sPath An optional sub-path to extract the arguments from
   */
  public function getOptions(XML_Element $oRoot, XML_Document $oSchema = null, $sPath = '') {
    
    $this->getElement($oRoot, $sPath);
    
    return new XML_Options(new XML_Document($oRoot), $oSchema);
  }
  
  public function getElement(XML_Element $oRoot, $sPath = '') {
    
    if ($sPath) $aArray = $this->get($sPath);
    else $aArray = $this->aArray;
    
    $this->buildElement($oRoot, $aArray);
  }
  
  private function buildElement(XML_Element $oParent, $aArray) {
    
    foreach ($aArray as $sKey => $mValue) {
      
      $oElement = $oParent->addNode($sKey);
      
      if (is_array($mValue)) $this->buildElement($oElement, $mValue);
      else $oElement->set($mValue);
    }
  }
  
  protected function log($sMessage, $sStatut = self::MESSAGES_STATUT) {
    
    Sylma::log($this->getNamespace(), $sMessage, $sStatut);
  }
}

