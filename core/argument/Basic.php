<?php

namespace sylma\core\argument;
use sylma\core;

/**
 * This class act as an interface to arrays of arrays/objects/strings with dom-like functions get/set/add
 * It can also be used with YAML files with the extended version @class XArguments
 */
abstract class Basic extends core\module\Namespaced implements core\argument {

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

  protected function createInstance($mPath) {

    return new static($mPath, $this->getNS(), $this);
  }

  //abstract public function locateValue(array $aPath = array(), $bDebug = true);

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

      //if ($arg = $val->asArgument()) {

        $mResult = static::normalizeArgument($val->asArgument(), $iMode);
      //}
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
