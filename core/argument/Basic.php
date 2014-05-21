<?php

namespace sylma\core\argument;
use sylma\core;

/**
 * This class act as an interface to arrays of arrays/objects/strings with dom-like functions get/set/add
 * It can also be used with YAML files with the extended version @class XArguments
 */
abstract class Basic extends core\module\Namespaced implements core\argument {

  /**
   * The default main array
   */
  protected $aArray = array();
  private $parent;

  public function __construct(array $aArray = array(), array $aNS = array(), core\argument $parent = null) {

    $this->setArray($aArray);

    $this->loadDefaultNamespace($aNS);

    $this->setNamespaces($aNS);
    if ($parent) $this->setParent($parent);
  }

  protected function loadDefaultNamespace(array $aNS) {

    if (isset($aNS[0]) && $aNS[0]) $this->setNamespace($aNS[0]);
  }

  protected function setArray(array $aContent) {

    $this->aArray = $aContent;
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

  public function getLength() {

    return count($this->aArray);
  }

  private function mergeArrays(array $aFrom, array $aTo, array $aPath = array()) {

    foreach($aTo as $sKey => $mVal) {

      if (0 && is_integer($sKey)) {

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

/*
  public function __toString() {

    $sResult = '';

    if ($this->aArray && count($this->aArray) == 1) {

      list(,$val) = each($this->aArray);
      $sResult = is_object($val) && method_exists($val, '__toString') ? (string) $val : gettype($val);
    }
    else {

      $sResult = '[error] Cannot render an argument as a string';
      //$this->throwException(sprintf('Cannot render an array as a string'));
    }

    return $sResult;
  }
 */
}
