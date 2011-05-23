<?php

class Arguments extends Namespaced implements ArgumentsInterface {
  
  const VARIABLE_PREFIX = '@sylma:';
  const MESSAGES_STATUT = Sylma::LOG_STATUT_DEFAULT;
  protected $aArray = array();
  
  /**
   * Store an error if occurs, in form : <code>array('name' => '', 'message' => '')</code>
   */
  protected static $aError = array();
  
  public function __construct(array $aArray = array(), $sNamespace = '') {
    
    if (is_array($aArray)) $this->aArray = $aArray;
    $this->sNamespace = $sNamespace;
  }
  
  public function set($sPath, $mValue = null) {
    
    if ($aTarget = $this->get($sPath)) {
      
      if ($mValue !== null) $aTarget = $mValue;
      else unset($aTarget);
    }
    
    return $aTarget;
  }
  
  public function get($sPath, $bDebug = true) {
    
    $mResult = null;
    
    if (!$sPath) $this->log(txt('Empty path is not valid'));
    else {
      
      $mResult = $this->getValue(self::parsePath($sPath));
      $aError = self::getError();
      
      if ($aError && $bDebug) $this->log($aError['name'] . ' - ' . $aError['message'], $sPath);
    }
    
    return $mResult;
  }
  
  protected static function parsePath($sPath, $sParent = '') {
    
    if ($sPath[0] == '/') $sPath = substr($sPath, 1);
    else if ($sParent) $sPath = $sParent . '/' . $sPath;
    
    if (strpos($sPath, '/') !== false) $aPath = explode('/', $sPath);
    else $aPath = array($sPath);
    
    $aResult = array();
    
    foreach ($aPath as $sPath) {
      
      if ($sPath != '..') $aResult[] = $sPath;
      else if (!$aResult) Sylma::log(self::NS, txt('Cannot use .. when current level is root in @path /%s', $sPath));
      else array_pop($aResult);
    }
    
    return $aResult;
  }
  
  protected function getValue(array $aPath = array()) {
    
    $mCurrent = $this->aArray;
    $mResult = null;
    $aParentPath = array();
    $sKey = '[none]';
    
    while ($aPath) {
      
      if (!is_array($mCurrent)) {
        
        self::setError('lost', txt('Key %s in %s', $sKey, implode('/', $aParentPath + $aPath)));
      }
      else {
        
        if ($sKey = $this->extractValue($mCurrent, $aPath, $aParentPath)) {
          
          $mCurrent =& $mCurrent[$sKey];
          
          // run hypotheticals parse on strings
          if (is_string($mCurrent)) $mCurrent = $this->parseValue($mCurrent, $aParentPath);
          
          // if last, save result
          if (!$aPath) $mResult = $mCurrent;
        }
      }
    }
    
    return $mResult;
  }
  
  protected function extractValue(array $aArray, array &$aPath, array &$aParentPath = array()) {
    
    $mResult = null;
    self::$aError = array();
    
    $sKey = array_shift($aPath);
    array_push($aParentPath, $sKey);
    
    if (!array_key_exists($sKey, $aArray)) {
      
      self::setError('unknown', txt('Key %s in %s', $sKey, implode('/', $aParentPath + $aPath)));
    }
    else {
      
      $mResult = $sKey;
    }
    
    return $mResult;
  }
  
  protected function parseValue($sValue, $aParentPath) {
    
    return $sValue;
  }
  
  protected static function getError() {
    
    return self::$aError;
  }
  
  protected static function setError($sName, $sArgument) {
    
    self::$aError = array(
      'name' => $sName,
      'message' => $sArgument,
    );
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

