<?php

namespace sylma\core\argument;
use sylma\core;

/**
 * This class act as an interface to arrays of arrays/objects/strings with dom-like functions get/set/add
 * It can also be used with YAML files with the extended version @class XArguments
 *
 * @author rodolphe.gerber (at) gmail.com
 */
abstract class Basic extends core\module\Namespaced implements core\argument {

  const MESSAGES_STATUT = \Sylma::LOG_STATUT_DEFAULT;
  const DEBUG_NORMALIZE_RECURSION = false;

  /**
   * The default main array
   */
  protected $aArray = array();
  private $parent;
  protected static $aNormalizedObjects = array();
  protected static $sCurrentPath;

  public function __construct(array $aArray = array(), array $aNS = array(), core\argument $parent = null) {

    if (is_array($aArray)) $this->aArray = $aArray;

    $this->setNamespaces($aNS);
    if ($parent) $this->setParent($parent);
  }

  public function getNamespace($sPrefix = null) {

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

    $mResult = null;
    $bRoot = false;

    if ($sPath !== '') {

      $aPath = $this->parsePath($sPath);

      if (is_null($mValue)) {

        $sLast = array_pop($aPath);

        if ($aPath) {

          $mTarget =& $this->locateValue($aPath, false, true);
        }
        else {

          $mTarget =& $this->aArray;
          //$bRoot = true;
        }

        if (is_array($mTarget)) {

          unset($mTarget[$sLast]);
        }
      }
      else {

        $mTarget =& $this->locateValue($aPath, false, true);

        if (is_null($mTarget)) {

          $mTarget =& $this->aArray;
          //$bRoot = true;
        }

        foreach ($aPath as $sKey) {

          $mTarget[$sKey] = array();
          $mTarget =& $mTarget[$sKey];
        }
      }
    }
    else {

      //$mTarget =& $this->aArray;
      $bRoot = true;
    }

    if ($bIndex) { // todo : check for usage

      if (is_array($mTarget)) {

        $mTarget[] = $mValue;
      }
      else if ($mTarget instanceof core\argument) {

        $mTarget->add($mValue);
      }
      else {

        $this->throwException('Cannot add a value in a non argument value in @path %s', $sPath);
      }
    }
    else {

      if ($bRoot) {

        if (is_null($mValue)) $this->aArray = array();
        else if ($mValue instanceof core\argument) $this->aArray = $mValue->query();
        else if (!is_array($mValue)) $this->aArray = array($mValue);
        else $this->aArray = $mValue;

      }
      else if (!is_null($mValue)) {

        $mTarget = $mValue;
      }
    }

//echo \Sylma::show(count($this->aArray));
    if ($mValue) {

      if ($sPath === '') {

        reset($this->aArray);
        $mResult =& $this->aArray;
        //$mResult =& end($this->aArray);//reset($this->aArray);
      }
      else if (is_object($mValue) || is_array($mValue)) {
//echo \Sylma::show($mValue);
//
        $mResult = $this->get($sPath);

      }
      else {

        $mResult = $this->read($sPath);
      }
    }

    return $mResult;
  }

  public function add($mValue) {

    return $this->aArray[] = $mValue;
  }

  public function shift($mValue) {

    return array_unshift($this->aArray, $mValue);
  }

  public function query($sPath = '', $bDebug = true) {

    if ($sPath) return (array) $this->getValue($sPath, $bDebug);
    else return $this->aArray;
  }

  public function get($sPath = '', $bDebug = true) {

    $mResult =& $this->getValue($sPath, $bDebug);

    return $this->parseGet($mResult, $sPath, $bDebug);
  }

  protected function parseGet(&$mResult, $sPath, $bDebug) {

    if (is_array($mResult)) {

      if ($sPath) $mResult = new static($mResult, $this->getNS(), $this);
      else return $this;
    }
    else if (is_scalar($mResult)) {

      if ($bDebug) $this->throwException(sprintf('%s is not an array', $sPath), 3);
      return null;
    }
    else {

      $mResult = $this->parseGetUnknown($mResult);
    }

    return $mResult;
  }

  protected function parseGetUnknown($mValue) {

    return $mValue;
  }

  /**
   * Calls getter's related method, it's an interface between @method get() and @method locateValue()
   *
   * @param? string $sPath The path to look for
   * @param? boolean $bDebug If set to FALSE, no exception will be thrown if path is incorrect
   *
   * @return null|mixed The value localized by path, or NULL
   */
  protected function &getValue($sPath = null, $bDebug = true) {

    $mResult = null;

    if ($sPath === null) {

      $mResult =& $this->aArray;
    }
    else {

      $aPath = self::parsePath($sPath);
      $mResult =& $this->locateValue($aPath, $bDebug);
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

      if ($sSubPath !== '..') $aResult[] = $sSubPath;
      else {

        if (!$aResult) \Sylma::throwException(sprintf('Cannot use .. when current level is root in @path /%s', $sSubPath));
        else array_pop($aResult);
      }
    }

    if ($sPath && !$aPath) $this->throwException(sprintf('Cannot parse path %s', $sPath));

    return $aResult;
  }

  public function &locateValue(array &$aPath = array(), $bDebug = true, $bReturn = false) {

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
              sprintf('No array in @path %s. Cannot browse with @path %s',
              implode('/', $aParentPath), implode('/', $aParentPath + $aPath)),
              count($aPath) + 3);
          }
        }
      }
      else {

        $sKey = $this->extractValue($mCurrent, $aPath, $aParentPath, $bDebug);

        if (!is_null($sKey)) {

          $mCurrent =& $mCurrent[$sKey];

          // run hypotheticals parse on strings
          if ($mCurrent) $mCurrent = $this->parseValue($mCurrent, $aParentPath);

          // if last, save result
          if (!$aPath) $mResult =& $mCurrent;
        }
        else {

          if ($bReturn) $mResult =& $mCurrent;
          break;
        }
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

    $sKey = array_shift($aPath);
    array_push($aParentPath, $sKey);

    if (!array_key_exists($sKey, $aArray) || $aArray[$sKey] === null) {

      array_unshift($aPath, $sKey);

      if ($bDebug) {

        $this->throwException(sprintf('Unknown key %s in @path %s', $sKey, implode('/', $aParentPath + $aPath)), count($aPath) + 5);
      }

      $sKey = null;
    }

    return $sKey;
  }

  /**
   * This methods does nothing as is.
   * It allows extended class to update value when loading, usefull with @class XArguments and YAML files
   *
   * @param string $mValue The value to edit
   * @param? array $aParentPath The path to the value
   *
   * @return string The same value as @param $sValue
   */
  protected function parseValue($mValue, array $aParentPath = array()) {

    return $mValue;
  }

  public function read($sPath = '', $bDebug = true) {

    $mResult =& $this->getValue($sPath, $bDebug);

    if (is_object($mResult) || is_array($mResult)) {

      $this->throwException(sprintf('%s is not a string', $sPath), 2);
    }

    return $mResult;
  }

  public function mergeArray(array $aArray) {

    $this->aArray = $this->mergeArrays($this->aArray, $aArray);
  }

  public function merge($mArgument) {

    if (is_array($mArgument)) {

      $this->mergeArray($mArgument);
    }
    else if ($mArgument instanceof core\argument) {

      $this->mergeArray($mArgument->query());
    }
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

  protected static function normalizeObject($val, $iMode) {
//echo '- ' .get_class($val).'<br/>';
    $mResult = null;

    if (self::DEBUG_NORMALIZE_RECURSION) {

      foreach (self::$aNormalizedObjects as $obj) {

        if ($obj === $val) {

          $formater = \Sylma::getControler('formater');
          \Sylma::throwException(sprintf('Recursion when normalizing with object : %s', $formater->asToken($val)));
        }
      }
    }

    if ($val instanceof core\argumentable) {

      $mResult = static::normalizeArgument($val->asArgument(), $iMode);
    }
    else if ($val instanceof core\argument) {

      if ($iMode & self::NORMALIZE_ARGUMENT) $mResult = static::normalizeArgument($val, $iMode);
      else $mResult = $val;
    }
    else {

      $mResult = static::normalizeObjectUnknown($val, $iMode);
    }

    if (self::DEBUG_NORMALIZE_RECURSION) self::$aNormalizedObjects[] = $val;

    return $mResult;
  }

  protected static function normalizeObjectUnknown($val, $iMode) {

    \Sylma::throwException(sprintf('Cannot normalize object @class %s', get_class($val)));
  }

  protected static function normalizeArgument(core\argument $arg, $bEmpty = false) {

    return $arg->asArray($bEmpty);
  }

  /**
   * Replace @class SettingsInterface and remove null values from array
   * @param array $aArray The array to use
   * @return array A new array with replaced values
   */
  public static function normalizeArray(array $aArray, $iMode = self::NORMALIZE_DEFAULT) {

    $aResult = array();
    $sCurrentPath = self::$sCurrentPath;

    foreach ($aArray as $sKey => $mVal) {

      self::$sCurrentPath = $sCurrentPath . '/' . $sKey;
      $mResult = self::normalizeValue($mVal, $iMode);

      if ($mResult !== null) $aResult[$sKey] = $mResult;
    }

    self::$sCurrentPath = $sCurrentPath;

    return $aResult;
  }

  protected static function normalizeValue($mValue, $iMode) {

    $mResult = null;

    if (is_object($mValue)) {

      $mResult = static::normalizeObject($mValue, $iMode);

      if (!$mResult) $mResult = null;
    }
    else if (is_array($mValue)) {

      $mResult = static::normalizeArray($mValue, $iMode);
      if (($iMode & self::NORMALIZE_EMPTY_ARRAY) && !$mResult) $mResult = null; // transform empty array to null
    }
    else {

      $mResult = static::normalizeUnknown($mValue, $iMode);
    }

    return $mResult;
  }

  protected static function normalizeUnknown($mVar, $iMode) {

    return $mVar;
  }

  public function normalize($iMode = self::NORMALIZE_DEFAULT) {

    self::$sCurrentPath = '';

    try {
      $this->aArray = static::normalizeArray($this->aArray, $iMode);
    }
    catch (core\exception $e) {

      $e->addPath('@last-path ' . self::$sCurrentPath);
      throw $e;
    }
  }

  protected function throwException($sMessage, $mSender = array(), $iOffset = 2) {

    \Sylma::throwException($sMessage, array('@namespace ' . $this->getNamespace()), $iOffset);
  }

  protected function log($sMessage, $sStatut = self::MESSAGES_STATUT) {

    \Sylma::log($this->getNamespace(), $sMessage, $sStatut);
  }

  public function asArray($bEmpty = false) {

    $iMode = self::NORMALIZE_DEFAULT;
    if (!$bEmpty) $iMode = $iMode & self::NORMALIZE_EMPTY_ARRAY;

    return static::normalizeArray($this->query(), $iMode);
  }

  public function asJSON() {

    return json_encode($this->asArray(true), \JSON_FORCE_OBJECT);
  }

  public function __toString() {

    $sResult = '';

    if (count($this->aArray) == 1) {

      list(,$val) = each($this->aArray);
      $sResult = (string) $val;
    }
    else {

      $sResult = '[error] Cannot render an array as a string';
      //$this->throwException(sprintf('Cannot render an array as a string'));
    }

    return $sResult;
  }
}
