<?php

/**
 * This class act as an interface to arrays with dom-like functions get/set/add
 * It can also be used with YAML files with the extended version @class XArguments
 * @author Rodolphe Gerber
 *
 */

class Arguments extends Namespaced implements SettingsInterface {
  
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
  
  public function set($sPath = '', $mValue = null) {
    
  	$aPath = $this->parsePath($sPath);
  	
    if ($mTarget =& $this->locateValue($aPath)) {
      
      if ($mValue !== null) $mTarget = $mValue;
      else unset($mTarget);
    }
    else if (($aError = self::getError()) && $aError['name'] == 'unknown') {
    	
    	foreach ($aPath as $sKey) {
    		
    		$mTarget[$sKey] = array();
    		$mTarget =& $mTarget[$sKey]; 
    	}
    	
    	$mTarget = $mValue;
    }
    
    return $mTarget;
  }
  
  public function query($sPath = '', $bDebug = true) {
    
    return (array) $this->getValue($sPath, $bDebug);
  }
  
  public function get($sPath = '', $bDebug = true) {
    
    $mResult = $this->getValue($sPath, $bDebug);
    
    if (!self::getError() && is_array($mResult)) {
      
      $mResult = new Arguments($mResult, $this->getNamespace());
    }
    
    return $mResult;
  }
  
  protected function getValue($sPath = '', $bDebug = true) {
    
    $mResult = null;
    
    if (!$sPath) {
      
      $mResult = $this->aArray;
    }
    else {
      
    	$aPath = self::parsePath($sPath);
    	
      $mResult = $this->locateValue($aPath);
      $aError = self::getError();
      
      if ($aError && $bDebug) {
      	
      	$this->log($aError['name'] . ' - ' . $aError['message'], $sPath);
      	$mResult = null;
      }
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
  
  protected function &locateValue(array &$aPath = array()) {
    
  	self::$aError = array();
    $mCurrent = $this->aArray;
    $mResult = null;
    $aParentPath = array();
    $sKey = '[none]';
    
    while ($aPath && !self::getError()) {
      
      if (!is_array($mCurrent)) {
        
      	$mResult = $mCurrent;
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
  
  public function read($sPath = '', $bDebug = true) {
    
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
  public function getOptions(DOMNode $oRoot, DOMDocument $oSchema = null, $sPath = '') {
    
    self::getElement($oRoot, $sPath);
    
    return new XML_Options(new XML_Document($oRoot), $oSchema);
  }
  
  public static function buildDocument(array $aArray, $sNamespace) {
    
    $root = new XML_Element('default', null, array(), $sNamespace);
    
    self::buildNode($root, $aArray);
    
    return new XML_Document($root->getFirst());
  }
  
  public function getElement(ElementInterface $root, $sPath = '') {
    
    if ($sPath) $aArray = $this->get($sPath);
    else $aArray = $this->aArray;
    
    self::buildNode($root, $aArray);
  }
  
  private static function buildNode(NodeInterface $parent, array $aArray) {
    
    foreach ($aArray as $sKey => $mValue) {
      
      if ($mValue) {
        
        if (is_integer($sKey)) {
          
          $node = $parent;
        }
        else {
          
          if ($sKey[0] == '@') {
            
            $parent->setAttribute(substr($sKey, 1), $mValue);
            continue;
          }
          else {
            
            $node = $parent->addNode($sKey);
          }
        }
        
        if (is_array($mValue)) self::buildNode($node, $mValue);
        else $node->add($mValue);
        
      }
    }
  }
  
  protected function log($sMessage, $sStatut = self::MESSAGES_STATUT) {
    
    Sylma::log($this->getNamespace(), $sMessage, $sStatut);
  }
}
