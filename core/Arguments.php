<?php

class Arguments implements ArgumentsInterface {
  
  const VARIABLE_PREFIX = '@sylma:';
  const NS = 'http://www.sylma.org/core/arguments';
  const MESSAGES_STATUT = Sylma::LOG_STATUT_DEFAULT;
  private $aArray = array();
  private $sNamespace = '';
  
  /**
   * Store an error if occurs, in form : <code>array('name' => '', 'message' => '')</code>
   */
  protected static $aError = array();
  
  public function __construct(array $aArray = array(), $sNamespace = '') {
    
    if (is_array($aArray)) $this->aArray = $aArray;
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
    
    $mResult = null;
    
    if (!$sPath) $this->log(txt('Empty path is not valid'));
    else {
      
      $mResult = self::getValue(self::parsePath($sPath), $this->aArray);
      $aError = self::getError();
      
      // if ($aError && $bDebug) $this->log($aError['name'] . ' - ' . $aError['message'], $sPath);
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
  
  protected static function getValue(array $aPath, array $aArray, array $aParentPath = array(), array $aRoot = array()) {
    
    if (!$aRoot) $aRoot = $aArray;
    $mResult = null;
    self::$aError = array();
    
    $sPath = implode('/', $aParentPath + $aPath);
    $sKey = array_shift($aPath);
    
    array_push($aParentPath, $sKey);
    
    if (!array_key_exists($sKey, $aArray)) {
      
      self::setError('unknown', txt('Key %s in %s', $sKey, $sPath));
      $mResult = null;
    }
    else {
      
      $mResult =& $aArray[$sKey];
      
      if (is_string($mResult)) $mResult = self::parseYAMLProperties($mResult, $aRoot, $aParentPath);
      
      if ($aPath) {
        
        if (is_array($aArray[$sKey])) $mResult = self::getValue($aPath, $mResult, $aParentPath, $aRoot);
        else self::setError('lost', txt('Key %s in %s', $sKey, $sPath));
      }
    }
    
    return $mResult;
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
  
  public static function loadYAML($sPath) {
    
    $aResult = array();
    
    if (!file_exists($sPath)) {
      
      Sylma::log(self::NS, txt('Cannot find configuration file in @file %s', $sPath), 'error');
    }
    else {
      
      if (!$sContent = file_get_contents($sPath)) {
        
        Sylma::log(self::NS, txt('@file %s is empty', $sPath), 'error');
      }
      else {
        
        $aResult = self::parseYAML($sContent);
      }
    }
    
    return $aResult;
  }
  
  public static function parseYAML($sContent) {
    
    $aResult = Spyc::YAMLLoadString($sContent);
    return $aResult;
  }
  
  protected static function parseTree(array $aArray) {
    
    ksort($aArray);
    
    
    $aResult = array();
    $aStack = $aArray;
    
    list($sCurrentKey, $mCurrentValue) = each($aArray);
    unset($aArray[$sCurrentKey]);
    
    foreach ($aStack as $sKey => $mValue) {
      // $aResult
    }
  }
  
  protected static function parseTreeItem(array $aArray, array $aResult) {
    
    $sValue = '';
    $iMaxSize = 0;
    
    foreach ($aArray as $sKey => $mValue) {
      
      if (!$iMaxSize) {
        
        foreach($sKey as $iKey => $sChar) {
          
          if ($sChar == $sKey[$iKey]) $iMaxSize = $iKey;
          else break;
        }
        
        if ($iMaxSize) {
          
          // if (is_array($mValue)
          $sValue = substr($sKey, 0, $iMaxSize);
          $aResult[$sValue] = 2;
        }
      }
      else {
        
        if (substr($sKey, 0, strlen($sValue)) == $sValue) {
          
          $aResult[$sValue]++;
        }
      }
    }
    
    // if (!$iMaxSize) $aResult[$sCurrentKey] = 
  }
  
  protected static function parseYAMLProperties($sValue, array $aRoot, array $aPath) {
    
    $mResult = $sValue;
    $iStart = strrpos($sValue, self::VARIABLE_PREFIX);
    
    while ($iStart !== false) {
      
      $sProperty = substr($sValue, $iStart);
      
      preg_match('/' . self::VARIABLE_PREFIX . '(\w+)\s*([^;]+);/', $sProperty, $aMatch);
      
      $mTempResult = self::parseYAMLProperty($aMatch[1], trim($aMatch[2]), $aRoot, $aPath);
      
      if ($iStart && is_string($mTempResult)) {
        
        $sValue = substr_replace($sValue, $mTempResult, $iStart, strlen($aMatch[0]));
      }
      else {
        
        $sValue = '';
        $mResult = $mTempResult;
      }
      
      $iStart = strrpos($sValue, self::VARIABLE_PREFIX);
    }
    
    return $mResult;
  }
  
  protected static function parseYAMLProperty($sName, $sArguments, array $aRoot, array $aPath) {
    
    $mResult = null;
    
    switch ($sName) {
      
      case 'import' :
        
        if (!$sPath = self::parseYAMLString($sArguments, $aRoot, $aPath)) {
          
          Sylma::log(self::NS, txt('Cannot load parameter for %s in %s', $sName, $sArguments), 'error');
        }
        else {
          
          $mResult = self::loadYAML(MAIN_DIRECTORY . $sPath);
        }
        
      break;
      
      case 'self' :
        
        $mResult = self::getValue(self::parsePath($sArguments, implode('/', $aPath)), $aRoot);
        //if ($aError = self::getError()) dspf($aError);
      break;
      
      default :
        
        Sylma::log(self::NS, txt('Unkown YAML property call : %s', $sName), 'error');
    }
    
    return $mResult;
  }
  
  protected static function parseYAMLString($sArguments, array $aRoot, array $aPath) {
    
    $aArguments = explode('+', $sArguments);
    $sResult = '';
    
    return implode('', array_map('trim', $aArguments));
  }
  
  protected function log($sMessage, $sStatut = self::MESSAGES_STATUT) {
    
    Sylma::log($this->getNamespace(), $sMessage, $sStatut);
  }
}

