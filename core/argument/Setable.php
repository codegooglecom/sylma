<?php

namespace sylma\core\argument;
use sylma\core;

/**
 * This class act as an interface to arrays of arrays/objects/strings with dom-like functions get/set/add
 * It can also be used with YAML files with the extended version @class XArguments
 */
class Setable extends Domed implements core\argument {

  const DEBUG_NORMALIZE_RECURSION = false;

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

      if ($sPath) {

        $mResult = $this->createInstance($mResult, $this->getNS());
      }
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
          else {

            break;
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

        $this->throwException(sprintf('Unknown key %s in @path %s', $sKey, implode('/', $aParentPath + $aPath)));
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
}
