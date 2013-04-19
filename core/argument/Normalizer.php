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
  protected static $sCurrentPath;

  protected function normalizeObject($val, $iMode) {
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

    if (self::DEBUG_NORMALIZE_RECURSION) self::$aNormalizedObjects[] = $val;

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
    $sCurrentPath = self::$sCurrentPath;

    foreach ($aArray as $sKey => $mVal) {

      self::$sCurrentPath = $sCurrentPath . '/' . $sKey;
      $mResult = $this->normalizeValue($mVal, $iMode);

      if ($mResult !== null) $aResult[$sKey] = $mResult;
    }

    self::$sCurrentPath = $sCurrentPath;

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

    self::$sCurrentPath = '';

    try {
      $this->aArray = $this->normalizeArray($this->aArray, $iMode);
    }
    catch (core\exception $e) {

      $e->addPath('@last-path ' . self::$sCurrentPath);
      throw $e;
    }
  }
}
