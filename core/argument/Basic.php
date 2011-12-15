<?php

namespace sylma\core\argument;
use \sylma\core;

require_once('core/argument.php');
require_once('core/argumentable.php');
require_once('core/module/Namespaced.php');

/**
 * This class act as an interface to arrays of arrays/objects/strings with dom-like functions get/set/add
 * It can also be used with YAML files with the extended version @class XArguments
 * 
 * @author rodolphe.gerber (at) gmail.com
 */
class Basic extends core\module\Namespaced implements core\argument, \Iterator {
  
  const MESSAGES_STATUT = \Sylma::LOG_STATUT_DEFAULT;
  
  /**
   * The default main array
   */
  protected $aArray = array();
  private $parent;
  
  public function __construct(array $aArray = array(), $sNamespace = '', core\argument $parent = null) {
    
    if (is_array($aArray)) $this->aArray = $aArray;
    
    $this->setNamespace($sNamespace);
    if ($parent) $this->setParent($parent);
  }
  
  public function getNamespace() {
    
    $sNamespace = parent::getNamespace();
    
    if (!$sNamespace && $this->getParent()) {
      
      $sNamespace = $this->getParent()->getNamespace();
    }
    
    return $sNamespace;
  }
  
  public function setParent(core\argument $parent) {
    
    $this->parent = $parent;
  }
  
  public function getParent() {
    
    return $this->parent;
  }
  
  public function set($sPath = '', $mValue = null, $bIndex = false) {
    
    if ($sPath) {
      
      $aPath = $this->parsePath($sPath);
      
      $mTarget =& $this->locateValue($aPath, false, true);
      
      if ($mTarget === null) $mTarget =& $this->aArray;
      
      foreach ($aPath as $sKey) {
        
        $mTarget[$sKey] = array();
        $mTarget =& $mTarget[$sKey]; 
      }
    }
    else {
      
      $mTarget =& $this->aArray;
    }
    
    if ($bIndex) {
      
      if (is_array($mTarget)) {
        
        $mTarget[] = $mValue;
      }
      else if ($mTarget instanceof core\argument) {
        
        $mTarget->add('', $mValue);
      }
      else {
        
        $this->throwException('Cannot add a value in a non argument value in @path %s', $sPath);
      }
    }
    else {
      
      if ($mValue !== null) {
        
        $mTarget = $mValue;
      }
      else {
        
        $mTarget = null;
      }
    }
    
    if ($mTarget !== null && !is_string($mValue)) {
      
      return $this->get($sPath);
    }
    else return null;
  }
  
  public function add($sPath = '', $mValue = null) {
    
    return $this->set($sPath, $mValue, true);
  }
  
  public function query($sPath = '', $bDebug = true) {
    
    return (array) $this->getValue($sPath, $bDebug);
  }
  
  /**
   * Load a pathed value and return it as argument object. It's opposite to @method read()
   * 
   * @param string $sPath The path to look for value
   * @param boolean $bDebug If TRUE, a result is expected and an exception is thrown if NULL
   *
   * @return core\argument|null The value located in the path as an object or NULL if none
   */
  public function get($sPath = '', $bDebug = true) {
    
    $mResult =& $this->getValue($sPath, $bDebug);
    
    if (is_array($mResult)) {
      
      if ($sPath) $mResult = new self($mResult, $this->getNamespace(), $this);
      else return $this;
    }
    else if (is_string($mResult)) {
      
      return null;
    }
    
    return $mResult;
  }
  
  /**
   * Calls getter's related method, it's an interface between @method get() and @method locateValue()
   *
   * @param? string $sPath The path to look for
   * @param? boolean $bDebug If set to FALSE, no exception will be thrown if path is incorrect
   *
   * @return null|mixed The value localized by path, or NULL
   */
  protected function &getValue($sPath = '', $bDebug = true) {
    
    $mResult = null;
    
    if (!$sPath) {
      
      $mResult =& $this->aArray;
    }
    else {
      
      try {
        
        $aPath = self::parsePath($sPath);
        $mResult =& $this->locateValue($aPath, $bDebug);
      }
      catch (core\exception $e) {
        
        $mResult = null;
        return $mResult;
      }
    }
    
    return $mResult;
  }
  
  /**
   * Split a path in an array of keys. Allow use of '..' to get upper levels
   *
   * @param string $sPath A relative or absolute path to split
   * @param? string $sParent The parent path if @param $sPath is relative
   *
   * @return array An array of keys
   */
  protected static function parsePath($sPath, $sParent = '') {
    
    if ($sPath && $sPath[0] == '/') $sPath = substr($sPath, 1);
    else if ($sParent) $sPath = $sParent . '/' . $sPath;
    
    if (strpos($sPath, '/') !== false) $aPath = explode('/', $sPath);
    else $aPath = array($sPath);
    
    $aResult = array();
    
    foreach ($aPath as $sSubPath) {
      
      if ($sSubPath != '..') $aResult[] = $sSubPath;
      else {
        
        if (!$aResult) $this->throwException(self::NS, txt('Cannot use .. when current level is root in @path /%s', $sSubPath));
        else array_pop($aResult);
      }
    }
    
    if ($sPath && !$aPath) $this->throwException(txt('Cannot parse path %s', $sPath));
    
    return $aResult;
  }
  
  /**
   * Main search method, it will go through the tree to localize value
   *
   * @param array $aPath The array of keys to look for
   * @param boolean $bDebug If set to FALSE, no exception will be thrown if path is incorrect
   * @param boolean $bReturn If set to TRUE, return the result even though path is incorrect
   *
   * @return null|mixed The value localized by path, or NULL
   */
  
  protected function &locateValue(array &$aPath = array(), $bDebug = true, $bReturn = false) {
    
    $mCurrent =& $this->aArray;
    $mResult = null;
    $aParentPath = array();
    $sKey = '[none]';
    
    while ($aPath) {
      
      if (!is_array($mCurrent)) {
        
        if ($mCurrent instanceof core\argument) {
          
          $mCurrent->setParent($this);
          $mResult =& $mCurrent->locateValue($aPath, $bDebug, $bReturn);
          break;
        }
        else {
          
          if ($bReturn) {
            
            $mResult =& $mCurrent;
            break;
          }
          else if ($aPath && $bDebug) {
            
            $this->throwException(
              txt('No array in @path %s. Cannot browse with @path %s',
              implode('/', $aParentPath), implode('/', $aParentPath + $aPath)),
              count($aPath) + 3);
          }
        }
      }
      else if ($sKey = $this->extractValue($mCurrent, $aPath, $aParentPath, $bDebug)) {
        
        $mCurrent =& $mCurrent[$sKey];
        
        // run hypotheticals parse on strings
        if ($mCurrent && is_string($mCurrent)) $mCurrent = $this->parseValue($mCurrent, $aParentPath);
        
        // if last, save result
        if (!$aPath) $mResult =& $mCurrent;
      }
      else {
        
        if ($bReturn) $mResult =& $mCurrent;
        break;
      }
    }
    
    return $mResult;
  }
  
  /**
   * Get next key in paths when traversing the tree
   * 
   * @param array $aArray The current array of values
   * @param array $aPath The current key's path
   * @param? array $aParentPath The parent key's
   * @param? boolean $bDebug if set to TRUE, no exception will be thrown if path is incorrect
   *
   * @return string The next valid key or empty if not found
   */
  protected function extractValue(array $aArray, array &$aPath, array &$aParentPath = array(), $bDebug = true) {
    
    $mResult = null;
    $sKey = array_shift($aPath);
    array_push($aParentPath, $sKey);
    
    if (!array_key_exists($sKey, $aArray) || $aArray[$sKey] === null) {
      
      array_unshift($aPath, $sKey);
      if ($bDebug) $this->throwException(txt('Unknown key %s in %s', $sKey, implode('/', $aParentPath + $aPath)), count($aPath) + 5);
      
      $sKey = '';
    }
    
    return $sKey;
  }
  
  /**
   * This methods does nothing as is.
   * It allows extended class to update value when loading, usefull with @class XArguments and YAML files
   * 
   * @param string $sValue The value to edit
   * @param? array $aParentPath The path to the value
   *
   * @return string The same value as @param $sValue
   */
  protected function parseValue($sValue, array $aParentPath = array()) {
    
    return $sValue;
  }
  
  public function read($sPath = '', $bDebug = true) {
    
    $mResult =& $this->getValue($sPath, $bDebug);
    
    if (is_object($mResult) || is_array($mResult)) {
      
      $this->throwException(txt('%s is not a string', $sPath), 2);
    }
    
    return $mResult;
  }
  
  public function mergeArray(array $aArray) {
    
    $this->aArray = $this->mergeArrays($this->aArray, $aArray);
  }
  
  /**
   * Recursively merge two argument objects, argument object received as argument (sic) will overwrite this one
   * @param core\argument $with The argument that will merge on this one
   */
  public function merge(core\argument $arg) {
    
    $this->mergeArray($arg->query());
  }
  
  private function mergeArrays(array $aFrom, array $aTo, array $aPath = array()) {
    
    foreach($aTo as $sKey => $mVal) {
      
      if (is_integer($sKey)) {
        
        $aFrom[] = $mVal;
      }
      else {
        
        if (array_key_exists($sKey, $aFrom)) {
          
          if (is_string($aFrom[$sKey]) && is_array($mVal)) {
            
            $aFrom[$sKey] = $this->parseValue($aFrom[$sKey], $aPath);
          }
          
          if (is_array($aFrom[$sKey]) && is_array($mVal)) {
            
            $aFrom[$sKey] = $this->mergeArrays($aFrom[$sKey], $mVal, $aPath + array($sKey));
          }
          else {
            
            $aFrom[$sKey] = $mVal;
          }
        }
        else {
          
          $aFrom[$sKey] = $mVal;
        }
      }
    }
    
    return $aFrom;
  }
  
  protected static function normalizeObject($val) {
    
    $mResult = null;
    
    if ($val instanceof core\argumentable) {
      
      $mResult = self::normalizeArgument($val->asArgument());
    }
    else if ($val instanceof core\argument) {
      
      $mResult = self::normalizeArgument($val);
    }
    else {
      
      \Sylma::throwException(txt('Cannot normalize object @class %s', get_class($val)));
    }
    
    return $mResult;
  }
  
  protected static function normalizeArgument(core\argument $arg) {
    
    return $arg->asArray();
  }
  /**
   * Replace @class SettingsInterface and remove null values from array
   * @param array $aArray The array to use
   * @return array A new array with replaced values
   */
  public static function normalizeArray(array $aArray) {
    
    $aResult = array();
    
    foreach ($aArray as $sKey => $mVal) {
      
      if (is_object($mVal)) {
        
        $mResult = static::normalizeObject($mVal);
        
        if (!$mResult) $mResult = null;
      }
      else if (is_array($mVal)) {
        
        $mResult = static::normalizeArray($mVal);
        
        if (!$mResult) $mResult = null; // transform empty array to null
      }
      else {
        
        $mResult = $mVal;
      }
      
      if ($mResult !== null) $aResult[$sKey] = $mResult;
    }
    
    return $aResult;
  }
  
  public function normalize($bKeepXML = false) {
    
    $this->aArray = static::normalizeArray($this->aArray);
  }
  
  public function rewind() {
    
    reset($this->aArray);
  }
  
  public function current() {
    
    $sKey = key($this->aArray);
    
    return $this->get($sKey);
  }
  
  public function key() {
    
    return key($this->aArray);
  }
  
  public function next() {
    
    next($this->aArray);
  }
  
  public function valid() {
    
    return current($this->aArray) !== false;
  }
  
  protected function throwException($sMessage, $iOffset = 1) {
    
    \Sylma::throwException($sMessage, array('@namespace ' . $this->getNamespace()), $iOffset);
  }
  
  protected function log($sMessage, $sStatut = self::MESSAGES_STATUT) {
    
    \Sylma::log($this->getNamespace(), $sMessage, $sStatut);
  }
  
  public function dsp() {
    
    return self::normalizeArray($this->aArray);
  }
  
  public function asArray() {
    
    return static::normalizeArray($this->query());
  }
  
  public function __toString() {
    
    $sResult = '';
    
    if (count($this->aArray) == 1) {
      
      list(,$val) = each($this->aArray);
      $sResult = (string) $val;
    }
    else {
      
      $this->log(txt('Cannot render an array as a string'));
    }
    
    return $sResult;
  }
}
