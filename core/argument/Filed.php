<?php

namespace sylma\core\argument;
use \sylma\core, sylma\storage\fs, sylma\core\functions\path;

require_once('Domed.php');
require_once('core/functions/Path.php');

require_once('spyc.php');

class Filed extends Domed {

  /**
   * Special calls use this prefix use in YAML files
   */
  const VARIABLE_PREFIX = '§sylma:';
  const DIRECTORY_TOKEN = '§sylma-directory';

  /**
   * See @method getControler()
   */
  const FILE_CONTROLER = 'fs';

  private $aTokens = array();
  private $aResultTokens = array();

  /**
   * File controler
   */
  private $controler;

  /**
   * File used in @method loadYAML()
   */
  private $file;
  private $sLastDirectory;

  public function __construct($mValue, array $aNS = array(), core\argument $parent = null) {

    // set namespace first for logging
    $this->setNamespaces($aNS);

    $aArray = array();

    if (is_string($mValue)) $aArray = $this->loadYAML($mValue, true);
    else if (is_array($mValue)) $aArray = $mValue;
    else $this->throwException(sprintf('Can only accepts array or string as first argument - given : %s', gettype($mValue)));
//if (count($aArray) == 1 && current($aArray) === null) dspf('error', 'error');
    parent::__construct($aArray, array(), $parent);
  }

  public function mergeFile($sPath) {

    if ($fs = $this->getControler()) {

      // file controler is ready
      if (!$file = $fs->getFile($sPath)) {

        $this->throwException(sprintf('Cannot find file @file %s to merge settings', $sPath));
      }

      $this->merge(self::loadYAML($file->getRealPath(), false));
    }
    else {

      // file controler is not ready
      $this->mergeArray(self::loadYAML($sPath, false));
    }
  }

  protected function parseValue($sValue, array $aParentPath = array()) {

    return $this->parseYAMLProperties($sValue, $aParentPath);
  }

  protected function extractValue(array $aArray, array &$aPath, array &$aParentPath = array(), $bDebug = true) {

    if ($this->aTokens) {

      foreach ($this->aTokens as $sToken => $mValue) {

        if (array_key_exists($sToken, $aArray)) {

          $this->setToken($sToken, $aArray[$sToken]);
          unset($aArray[$sToken]);
        }
      }
    }

    return parent::extractValue($aArray, $aPath, $aParentPath, $bDebug);
  }

  public function get($sPath = '', $bDebug = true) {

    $rResult =& $this->getValue($sPath, $bDebug);

    if (is_array($rResult)) {

      $rResult = new self($rResult, $this->getNS(), $this);

      // copy tokens
      $rResult->aTokens = $this->aTokens;
      $rResult->aResultTokens = $this->aResultTokens;
      if ($this->getFile()) $rResult->setFile($this->getFile());
    }
    else if (!is_object($rResult) && !is_null($rResult)) {

      if ($bDebug) $this->throwException(sprintf('%s is not an array', $sPath), 3);
      return null;
    }

    return $rResult;
  }

  /**
   * Load content of the file as the YAML content. Allow multiple loads, for stepped controler loads
   *
   * @param type $sPath Path to the file
   * @param type $bFirstLoad If set to TRUE, the file will be replaced by the new one loaded
   * @return array The YAML datas
   */
  protected function loadYAML($sPath, $bFirstLoad = true) {

    $aResult = array();

    try {

      if ($fs = $this->getControler()) {

        // file controler is ready

        if ($file = $this->getFile()) {

          require_once('core/functions/Path.php');
          $sPath = path\toAbsolute($sPath, (string) $file->getParent());
        }

        $file = $fs->getFile($sPath);

        if (!$sContent = $file->execute()) {

          $this->throwException(sprintf('@file %s is empty', $file));
        }

        if ($bFirstLoad) $this->setFile($file);
        $aResult = $this->parseYAML($sContent);
      }
      else {

        // file controler is not ready
        $aResult = $this->loadYAMLFree($sPath);
      }
    }
    catch (core\exception $e) {

      throw $e;
      //return null;
    }

    return $aResult;
  }

  /**
   * Determine if fs module is ready
   */
  protected function getControler() {

    return \Sylma::getControler(self::FILE_CONTROLER, false, false);
  }

  protected function setFile(fs\file $file) {

    $this->file = $file;
  }

  public function getFile() {

    return $this->file;
  }

  public function getToken($sToken) {

    if (array_key_exists($sToken, $this->aResultTokens)) return $this->aResultTokens[$sToken];
    else return null;
  }

  public function setToken($sKey, $mValue) {

    $this->aResultTokens[$sKey] = $mValue;
    if ($this->getParent()) $this->getParent()->setToken($sKey, $mValue);
  }

  public function unRegisterToken($sToken) {

    if (array_key_exists($sToken, $this->aTokens)) unset($this->aTokens[$sToken]);
  }

  public function registerToken($sToken) {

    $this->aTokens[$sToken] = null;
  }

  public function getLastDirectory() {

    if ((!$sResult = $this->getToken(self::DIRECTORY_TOKEN)) && $this->getFile()) $sResult = (string) $this->getFile()->getParent();
    if (!$sResult && $this->getParent()) $sResult = $this->getParent()->getLastDirectory();

    return $sResult;
  }

  protected function loadYAMLFree($sPath) {

    $aResult = array();

    //$sPath = ($sPath{0} != '/' ? '/' : '') . $sPath; //\Sylma::ROOT .

    if (!file_exists($sPath)) {

      $this->throwException(sprintf('Cannot find configuration file in @file %s', $sPath));
    }

    if (!$sContent = file_get_contents($sPath)) {

      $this->throwException(sprintf('@file %s is empty', $sPath));
    }

    return $this->parseYAML($sContent);
  }

  protected function parseYAML($sContent) {

    $aResult = \Spyc::YAMLLoadString($sContent);
    return $aResult;
  }

  protected function parseYAMLProperties($sValue, array $aPath) {

    // TODO : strange bug, with @ as first char of value. See /system/sylma.yml/actions
    if (ord($sValue{0}) === 0) $sValue{0} = '§';

    $mResult = $sValue;
    $iStart = strrpos($sValue, self::VARIABLE_PREFIX);

    while ($iStart !== false) {

      $sProperty = substr($sValue, $iStart);

      preg_match('/' . self::VARIABLE_PREFIX . '(\w+)\s*([^;]+);/', $sProperty, $aMatch);

      $mTempResult = $this->parseYAMLProperty($aMatch[1], trim($aMatch[2]), $aPath);

      if ($iStart && is_string($mTempResult)) {

        $sValue = substr_replace($sValue, $mTempResult, $iStart, strlen($aMatch[0]));
      }
      else {

        $sValue = '';
        $mResult = $mTempResult;
      }

      $iStart = strrpos($sValue, self::VARIABLE_PREFIX);
    }

    return $mResult;
  }

  protected function parseYAMLProperty($sName, $sArguments, array $aPath) {

    $mResult = null;

    switch ($sName) {

      case 'import' :

        if (!$sPath = $this->parseYAMLString($sArguments)) {

          $this->throwException(sprintf('Cannot load parameter for %s in %s', $sName, $sArguments));
        }

        if (!$this->getControler()) $sPath = \Sylma::ROOT . $sPath;

        $mResult = self::loadYAML($sPath, false);
        if (is_array($mResult)) $mResult[self::DIRECTORY_TOKEN] = dirname($sPath);

      break;

      case 'self' :

      	$aPath = self::parsePath($sArguments, implode('/', $aPath));
        $mResult = $this->locateValue($aPath);

      break;

      default :

        $this->throwException(sprintf('Unkown YAML property call : %s', $sName));
    }

    return $mResult;
  }

  protected function parseYAMLString($sArguments) {

    $aArguments = explode('+', $sArguments);

    return implode('', array_map('trim', $aArguments));
  }

  public function merge(core\argument $arg) {

    if ($arg instanceof self) $this->file = $arg->getFile();

    return parent::merge($arg);
  }

  public function dump() {

    $this->normalize();

    return \Spyc::YAMLDump($this->aArray);
  }
}
