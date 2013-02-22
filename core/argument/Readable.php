<?php

namespace sylma\core\argument;
use sylma\core;

/**
 * This class act as an interface to arrays of arrays/objects/strings with dom-like functions get/set/add
 * It can also be used with YAML files with the extended version @class XArguments
 */
class Readable extends Domed implements core\argument {

  public function query($sPath = '', $bDebug = true) {

    if ($sPath) return (array) $this->getValue($sPath, $bDebug);
    else return $this->aArray;
  }

  public function get($sPath = '', $bDebug = true) {

    $mValue = $this->getValue($sPath, $bDebug);

    if (is_array($mValue)) {

      $mResult = $this->createInstance($mValue);
    }
    else if (is_object($mValue)) {

      $mResult = $mValue;
    }
    else {

      if ($bDebug) {

        $this->throwException(sprintf('No argument or array found at path "%s"', $sPath));
      }

      $mResult = null;
    }

    return $mResult;
  }

  public function read($sPath = '', $bDebug = true) {

    return $this->getValue($sPath, $bDebug);
  }

  public function set($sPath, $mValue) {

    if (!$aPath = $this->parsePath($sPath)) {

      $this->throwException('Cannot set without path');
    }

    $mCurrent =& $this->aArray;

    do {

      $sKey = current($aPath);
      $mCurrent =& $mCurrent[$sKey];

    } while(next($aPath));

    if (each($aPath)) {

      $this->throwException(sprintf('Cannot find path "%s" to set value'));
    }

    $mCurrent = $mValue;
  }

  /**
   * Calls getter's related method, it's an interface between @method get() and @method locateValue()
   *
   * @param? string $sPath The path to look for
   * @param? boolean $bDebug If set to FALSE, no exception will be thrown if path is incorrect
   *
   * @return null|mixed The value localized by path, or NULL
   */
  protected function getValue($sPath = null, $bDebug = true) {

    if (is_null($sPath)) {

      $mResult = $this->aArray;
    }
    else {

      $aPath = $this->parsePath($sPath);
      $mResult = $this->locateValue($aPath, $bDebug);
    }

    return $mResult;
  }

  public function locateValue(array &$aPath, $bDebug) {

    $mCurrent = $this->aArray;

    do {

      $sKey = current($aPath);
      $mCurrent = $this->parseValue($aPath, $mCurrent, $bDebug);

      if ($sKey !== false && is_array($mCurrent) && array_key_exists($sKey, $mCurrent)) {

        $mCurrent = $mCurrent[$sKey];
      }
      else {

        if (is_null($mCurrent)) {

          break;
        }
      }

    } while (each($aPath));

    if (each($aPath) && $bDebug) {

      $this->throwException(sprintf('Path "%s" not found', implode('/', $aPath)));
    }
    else if (is_null($mCurrent) && $bDebug) {

      $this->throwException(sprintf('No result for path "%s"', implode('/', $aPath)));
    }
    else {

      $mResult = $mCurrent;
    }

    return $mResult;
  }

  protected function parsePath($sPath) {

    if (strpos($sPath, '/') !== false) $aResult = explode('/', $sPath);
    else $aResult = array($sPath);

    return $aResult;
  }

  protected function parseValue(array &$aPath, $mValue, $bDebug) {

    if ($mValue instanceof core\argument) {

      $mResult = $mValue->locateValue($aPath, $bDebug);
    }
    else {

      $mResult = $mValue;
    }

    return $mResult;
  }
}
