<?php

class Arguments {
  
  const MESSAGES_STATUT = 'warning';
  private $aArray = array();
  private $sName = '';
  
  public function __construct(array $aArray, $sName = '') {
    
    $this->aArray = $aArray;
    $this->sName = $sName;
  }
  
  public function getName() {
    
    return $this->sName;
  }
  
  public function set($sPath, $mValue) {
    
    if ($aTarget = $this->get($sPath)) {
      
      if ($mValue) $aTarget = $mValue;
      else unset($aTarget);
    }
    
    return $aTarget;
  }
  
  public function &get($sPath, $mDefault = null, $bDebug = true) {
    
    if (!$sPath) $this->dspm('Aucun chemin indiqué dans la requête', 'warning');
    else {
      
      if ($sPath[0] == '/') $sPath = substr($sPath, 1);
      
      if (strpos($sPath, '/') !== false) $aPath = explode('/', $sPath);
      else $aPath = array($sPath);
      
      return $this->getValue($aPath, $this->aArray, $mDefault, $bDebug, $sPath);
    }
    
    return null;
  }
  
  private function &getValue(array $aPath, array $aArray, $mDefault, $bDebug, $sPath) {
    
    $mResult = null;
    $sKey = array_shift($aPath);
    
    if (!array_key_exists($sKey, $aArray)) {
      
      if ($bDebug) $this->dspm("Cannot find '$sPath', stopped at key '$sKey'");
      
      if ($mDefault !== null) $mResult = $mDefault;
      else $mResult = null;
    }
    else {
      
      if (!$aPath) $mResult = $aArray[$sKey];
      else if (is_array($aArray[$sKey])) $mResult = $this->getValue($aPath, $aArray[$sKey], $mDefault, $bDebug, $sPath);
      else $this->dspm("Aucun sous-chemin dans $sPath");
    }
    
    return $mResult;
  }
  
  public function read($sPath, $mDefault = null, $bDebug = true) {
    
    $sResult = $this->get($sPath, $mDefault, $bDebug);
    
    if (is_array($sResult)) {
      
      $this->dspm("Cannot read array in $sPath");
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
  
  protected function dspm($sMessage, $sStatut = self::MESSAGES_STATUT) {
    
    dspm($sMessage." - Arguments [{$this->sName}]", $sStatut);
  }
}

