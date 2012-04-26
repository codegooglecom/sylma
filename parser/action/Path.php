<?php

namespace sylma\parser\action;
use sylma\core, sylma\storage\fs;

require_once('core/module/Argumented.php');

class Path extends core\module\Argumented {

  private $sPath = '';
  private $file = null;

  protected $sExtension = '';

  const NS = 'http://www.sylma.org/parser/action/path';
  /**
   * @param string $sPath Path to look for an action
   * @param array $aArguments List of any arguments to add to the path
   * @param boolean $bParse Look for the correct file path through directories
   * @param $bArguments Use of indexed arguments (file/argument1/argument2)
   * @param $bDebug throw exceptions on error
   */

  public function __construct($sPath, fs\directory $directory = null, array $aArguments = array(), $bParse = true, $bArguments = true, $bDebug = true) {

    $this->setControler($this->getControler('action'));

    $this->setPath($sPath);
    $this->setNamespace(self::NS);

    $this->setArguments($aArguments);

    // Remove arguments following '?' of type ..?arg1=val&arg2=val..
    $this->getArguments()->mergeArray($this->extractArguments($sPath));

    if ($bParse) $this->parsePath($bArguments, $bDebug);
  }

  protected function extractArguments($sPath) {

    $aResult = array();

    if ($iAssoc = strpos($sPath, '?')) {

      $sAssoc = substr($sPath, $iAssoc + 1);
      $sPath = substr($sPath, 0, $iAssoc);

      $aAssoc = explode('&', $sAssoc);

      foreach ($aAssoc as $sArgument) {

        $aArgument = explode('=', $sArgument);

        if (count($aArgument) == 1) $aResult[] = $this->parseBaseType($aArgument[0]); // index : only name
        else $aResult[$aArgument[0]] = $this->parseBaseType($aArgument[1]); // assoc : name and value
      }
    }

    return $aResult;
  }

  public function parsePath($bArguments = true, $bDebug = true) {

    $controler = $this->getControler();

    $file = null;
    $dir = $controler->getDirectory('/');

    $aPath = $this->getPath(true);

    do {

      $sSubPath = $aPath ? $aPath[0] : '.';

      if (!$sub = $dir->getDirectory($sSubPath, false)) {

        $file = $this->findAction($dir, $sSubPath, $bArguments, $bDebug);

      } else {

        $dir = $sub;
      }

      if (!$file && (!$aPath || !$sub)) {

        if (!$file = $this->findAction($dir, 'index', $bArguments, $bDebug)) {

          if ($dir->checkRights(MODE_EXECUTION)) {

            $controler->throwException(txt('No index file in %s', $dir->asToken()));

          } else {

            $controler->throwExecution(txt('No execution rights on %s', $dir->asToken()));
          }
        }
      }

      array_shift($aPath);

    } while (!$file && $aPath);

    $aPath = $this->loadIndexed($aPath);

    $this->setFile($file);
    $this->getArguments()->mergeArray($aPath);
  }

  protected function loadIndexed(array $aPath) {

    $aResult = array();

    foreach ($aPath as $sValue) {

      if ($sValue) $aResult[] = $this->parseBaseType($sValue);
    }

    return $aResult;
  }

  protected function findAction(fs\directory $dir, $sPath, $bArguments, $bDebug) {

    $exts = $this->getControler()->getArgument('extensions');
    $result = null;

    if (!$bArguments && $bDebug) {

      $this->throwException(sprintf('Directory %s not found in path %s', $sPath, $this->getPath()));
    }

    foreach ($exts->asArray() as $sExtension) {

      if ($result = $dir->getFile($sPath . '.' . $sExtension, false)) break;
    }

    return $result;
  }

  protected function parseBaseType($sValue) {

    $mResult = $sValue;

    if (is_string($sValue) && strpos($sValue, 'xs:') !== false) {

      $aMatches = array();
      preg_match('/^xs:(\w+)\(([^\)]+)\)$/', $sValue, $aMatches);

      switch ($aMatches[1]) {

        case 'bool' :
        case 'boolean' :

          $mResult = strtobool($aMatches[2]);

        break;

        case 'int' :
        case 'integer' :

          $mResult = (int) $aMatches[2];

        break;

        default :

          $this->dspm(xt('Unknown base type %s', new HTML_Strong($aMatches[1])), 'warning');
      }
    }

    return $mResult;
  }

  public function parseExtension($bRemove) {

    $sPath = $this->getPath();

    preg_match('/\.(\w+)$/', $sPath, $aResult, PREG_OFFSET_CAPTURE);

    if (count($aResult) == 2 && ($sExtension = $aResult[1][0])) {

      $iExtension = $aResult[1][1];
      if ($bRemove) $this->setPath(substr($sPath, 0, $iExtension - 1).substr($sPath, $iExtension + strlen($sExtension)));

      $this->sExtension = $sExtension;
    }

    return $this->getExtension();
  }

  public function getExtension() {

    return $this->sExtension;
  }

  public function getFile() {

    return $this->file;
  }

  public function getArgument($sPath, $mDefault = null, $bDebug = false) {

    return parent::getArgument($sPath, $bDebug);
  }

  public function getArgumentsArray() {

    $args = $this->getArguments();

    return $args->asArray();
  }

  protected function setFile(fs\file $file) {

    $this->file = $file;
  }

  protected function setPath($sPath) {

    $sPath = str_replace('__', '..', $sPath); // tmp until parseGet ^ available

    if ($sPath{0} != '/') {

      $this->throwException(txt('Invalid path : %s', $sPath));
    }

    $this->sPath = $sPath;
  }

  protected function getPath($bArray = false) {

    $mResult = null;

    if ($bArray) {

      if ($this->getPath() == '/') {

        $mResult = array();
      }
      else {

        $mResult = explode('/', $this->getPath());
        array_shift($mResult);
      }
    }
    else {

      $mResult = $this->sPath;
    }

    return $mResult;
  }

  protected function throwException($sMessage, $mSender = array(), $iOffset = 2) {

    $mSender[] = '@path ' . $this->getPath();

    return $this->getControler()->throwException($sMessage, $mSender);
  }

  public function __toString() {

    return $this->sPath;
  }
}




