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

    $mValue =& $this->getValue($sPath, $bDebug);

    if (is_array($mValue)) {

      $mResult = $mValue = $this->createInstance($mValue);
    }
    else if (is_object($mValue)) {

      $mResult = $mValue;
    }
    else {

      if ($bDebug) {

        $this->throwException(sprintf('No argument or array found at path "%s"', $sPath));
      }

      $mResult = array();
    }

    return $mResult;
  }

  protected function parseResult(&$mValue, $bDebug = true, $bNullize = false, $sPath = '') {


    //return $mResult;
  }

  public function read($sPath = '', $bDebug = true) {

    return $this->getValue($sPath, $bDebug);
  }

  public function setPath(array &$aPath, $mValue = null, $bRef = false) {

    $mCurrent =& $this->aArray;
    $bNULL = is_null($mValue);

    do {

      $sKey = current($aPath);
//echo is_object($mCurrent) ? get_class($mCurrent) : print_r($mCurrent);
      if ($mCurrent instanceof core\argument) {

        return $mCurrent->setPath($aPath, $mValue, $bRef);
      }
      else if (!is_array($mCurrent)) {

        $this->launchException("Cannot set value to : " . \Sylma::show($mCurrent), get_defined_vars());
      }

      if (!array_key_exists($sKey, $mCurrent)) $mCurrent[$sKey] = array();

      $mPrevious =& $mCurrent;
      $mCurrent =& $mCurrent[$sKey];

    } while(next($aPath));

    if (each($aPath)) {

      $this->throwException(sprintf('Cannot find path "%s" to set value', $sPath));
    }

    if ($bNULL) {

      unset($mPrevious[$sKey]);
    }
    else {

      $mCurrent = $bRef && is_array($mValue) ? $this->createInstance($mValue) : $mValue;
    }

    return $mCurrent;
  }

  public function set($sPath, $mValue = null, $bRef = false) {

    if (!$aPath = $this->parsePath($sPath)) {

      $this->throwException('Cannot set without path');
    }

    return $this->setPath($aPath, $mValue, $bRef);
  }

  public function shift() {

    $mVal = array_shift($this->aArray);

    return $mVal;
  }

  public function getFirst() {

    return reset($this->aArray);
  }

  public function add($mValue, $bRef = false) {

    if ($bRef && is_array($mValue)) {

      $mValue = $this->createInstance($mValue);
    }

    $this->aArray[] = $mValue;

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

    if (is_null($sPath)) {

      $mResult =& $this->aArray;
    }
    else {

      $aPath = $this->parsePath($sPath);
      $mResult =& $this->locateValue($aPath, $bDebug);
    }

    return $mResult;
  }

  public function &locateValue(array &$aPath, $bDebug) {

    $mCurrent =& $this->aArray;
    $mResult = null;

    do {

      $sKey = current($aPath);
      $mCurrent =& $this->parseValue($aPath, $mCurrent, $bDebug);

      $bArray = is_array($mCurrent);

      if ($bArray && array_key_exists($sKey, $mCurrent)) {

        $mCurrent =& $mCurrent[$sKey];
        $mResult = $mCurrent;
      }
      else {

        if (key($aPath) !== false && (is_null($mCurrent) || $bArray)) {

          if ($bDebug) {

            $this->launchException(sprintf('No result for path "%s"', implode('/', $aPath)), get_defined_vars());
          }

          $mResult = null;
          break;
        }
      }

    } while (next($aPath));

    if (each($aPath) && $bDebug) {

      $this->launchException(sprintf('Path "%s" not found', implode('/', $aPath)), get_defined_vars());
    }
    else if (!is_null($mResult)) {

      $mResult =& $mCurrent;
    }

    return $mResult;
  }

  protected function parsePath($sPath) {

    if (strpos($sPath, '/') !== false) $aResult = explode('/', $sPath);
    else $aResult = array($sPath);

    return $aResult;
  }

  protected function &parseValue(array &$aPath, &$mValue, $bDebug) {

    if ($mValue instanceof core\argument) {

      $mResult =& $mValue->locateValue($aPath, $bDebug);
    }
    else {

      $mResult =& $mValue;
    }

    return $mResult;
  }

  protected function launchException($sMessage, array $aVars = array(), array $mSender = array()) {

    $aVars[] = $this;

    parent::launchException($sMessage, $aVars, $mSender);
  }
}
