<?php

namespace sylma\core\argument;
use sylma\core;

/**
 * This class act as an interface to arrays of arrays/objects/strings with dom-like functions get/set/add
 * It can also be used with YAML files with the extended version @class XArguments
 */
abstract class Normalizer extends Basic {

  const DEBUG_NORMALIZE_RECURSION = false;

  protected static $aNormalizedObjects = array();

  protected function normalizeObject($val, $iMode) {

    $mResult = null;

    if (self::DEBUG_NORMALIZE_RECURSION) {

      foreach (self::$aNormalizedObjects as $obj) {

        if ($obj === $val) {

          \Sylma::throwException("Recursion when normalizing with object : " . \Sylma::show($val));
        }
      }
    }

    if ($val instanceof core\argumentable) {

      if ($arg = $val->asArgument()) {

        $mResult = $this->normalizeArgument($arg, $iMode);
      }
    }
    else if ($val instanceof core\argument) {

      if ($iMode & self::NORMALIZE_ARGUMENT) $mResult = $this->normalizeArgument($val, $iMode);
      else $mResult = $val;
    }
    else {

      $mResult = $this->normalizeObjectUnknown($val, $iMode);
    }

    if (self::DEBUG_NORMALIZE_RECURSION) {

      self::$aNormalizedObjects[] = $val;
    }

    return $mResult;
  }

  protected function normalizeObjectUnknown($val, $iMode) {

    \Sylma::throwException(sprintf('Cannot normalize object @class %s', get_class($val)));
  }

  protected function normalizeArgument(core\argument $arg, $bEmpty = false) {

    return $arg->asArray($bEmpty);
  }

  /**
   * Replace @class SettingsInterface and remove null values from array
   * @param array $aArray The array to use
   * @return array A new array with replaced values
   */
  public function normalizeArray(array $aArray, $iMode = self::NORMALIZE_DEFAULT) {

    $aResult = array();

    foreach ($aArray as $sKey => $mVal) {

      $mResult = $this->normalizeValue($mVal, $iMode);

      if ($mResult !== null) $aResult[$sKey] = $mResult;
    }

    return $aResult;
  }

  protected function normalizeValue($mValue, $iMode) {

    $mResult = null;

    if (is_object($mValue)) {

      $mResult = $this->normalizeObject($mValue, $iMode);
      if (!$mResult) $mResult = null;
    }
    else if (is_array($mValue)) {

      $mResult = $this->normalizeArray($mValue, $iMode);
      if (($iMode & self::NORMALIZE_EMPTY_ARRAY) && !$mResult) $mResult = null; // transform empty array to null
    }
    else {

      $mResult = $this->normalizeUnknown($mValue, $iMode);
    }

    return $mResult;
  }

  protected function normalizeUnknown($mVar, $iMode) {

    return $mVar;
  }

  public function normalize($iMode = self::NORMALIZE_DEFAULT) {

    $this->aArray = $this->normalizeArray($this->aArray, $iMode);
  }

  public function asArray($bEmpty = false) {

    $iMode = self::NORMALIZE_DEFAULT;
    if (!$bEmpty) $iMode = $iMode & self::NORMALIZE_EMPTY_ARRAY;

    //return current($this->aArray) ? $this->normalizeArray($this->query(), $iMode) : $this->aArray;
    return $this->normalizeArray($this->query(), $iMode);
  }

  public function asJSON($iMode = \JSON_FORCE_OBJECT) {

    return json_encode($this->asArray(true), $iMode);
  }
}
