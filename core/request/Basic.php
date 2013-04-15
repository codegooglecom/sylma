<?php

namespace sylma\core\request;
use sylma\core, sylma\storage\fs, sylma\core\functions;

class Basic extends core\module\Filed implements core\request {

  const FILE_MANAGER = 'fs';

  private $sPath = '';

  protected $sExtension = '';
  protected $aExtensions = array();

  protected $config;

  const NS = 'http://www.sylma.org/parser/action/path';
  /**
   * @param string $sPath Path to look for an action
   * @param array $aArguments List of any arguments to add to the path
   * @param boolean $bParse Look for the correct file path through directories
   * @param $bArguments Use of indexed arguments (file/argument1/argument2)
   * @param $bDebug throw exceptions on error
   */

  public function __construct($sPath, fs\directory $directory = null, array $aArguments = array(), $bParse = true, array $aExtensions = array()) {

    if ($directory) {

      require_once('core/functions/Path.php');
      $sPath = functions\path\toAbsolute($sPath, $directory);
    }

    $this->setPath($sPath);
    $this->setNamespace(self::NS);

    $this->setSettings(include('arguments.xml.php'));
    $this->setArguments($aArguments);

    //if (!$aExtensions) $aExtensions = $this->getManager('init')->getExtensions();
    //$this->setExtensions($aExtensions);

    // Remove arguments following '?' of type ..?arg1=val&arg2=val..
    //$this->getArguments()->mergeArray($this->extractArguments($sPath));

    if ($bParse) $this->parse();
  }

  protected function setSettings(core\argument $arg) {

    $this->settings = $arg;
  }

  protected function getSettings() {

    return $this->settings;
  }

  protected function query($sPath) {

    return $this->settings->query($sPath);
  }

  public function asFile() {

    $this->setDirectory(__FILE__);

    if ($result = $this->getFile($this->getPath(), false)) {

      if (!in_array($result->getExtension(), $this->query('extensions/readable'))) {

        $this->throwException('Unauthorized extension type');
      }
    }

    return $result;
  }

  /**
   * Used for quicker access to extensions
   */
  public function getExtensions() {

    return $this->aExtensions;
  }

  public function setExtensions($aExtensions) {

    $this->aExtensions = $aExtensions;
  }
/*
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
*/
  public function parse() {

    $file = null;
    $dir = $this->getManager(self::FILE_MANAGER)->getDirectory('/');
    $this->setExtensions($this->query('extensions/executable'));

    $aPath = $this->getPath(true);

    do {

      $sub = null;
      $sSubPath = $aPath ? $aPath[0] : '';

      if ($sSubPath) {

        if (!$sub = $dir->getDirectory($sSubPath, false)) {

          $file = $this->findAction($dir, $sSubPath);
        }
        else {

          $dir = $sub;
          array_shift($aPath);
        }
      }

      if (!$sub) {

        if (!$file) {

          $file = $this->findAction($dir, 'index');

          if (!$file) {

            $this->throwException(sprintf("No index file or no '%s' directory in %s", $sSubPath, $dir->asToken()));
          }
        }
        else {

          array_shift($aPath);
        }
      }

    } while (!$file && $sub);

    //$aPath = $this->loadIndexed($aPath);
    if ($aPath) {

      $sFile = $file ? $file->asToken() : '[no file found]';
      $this->throwException(sprintf('Too much arguments sent to %s', $sFile));
    }

    $this->setFile($file);
    //$this->getArguments()->mergeArray($aPath);
  }

  protected function loadIndexed(array $aPath) {

    $aResult = array();

    foreach ($aPath as $sValue) {

      if ($sValue) $aResult[] = $this->parseBaseType($sValue);
    }

    return $aResult;
  }

  protected function findAction(fs\directory $dir, $sPath) {

    $result = null;

    foreach ($this->getExtensions() as $sExtension) {

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

  public function getArgument($sPath, $bDebug = true, $mDefault = null) {

    return parent::getArgument($sPath, $bDebug, $mDefault);
  }

  protected function setPath($sPath) {

    $sPath = str_replace('__', '..', $sPath); // tmp until parseGet ^ available

    if ($sPath{0} != '/') {

      $this->throwException(sprintf('Invalid path : %s', $sPath));
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

    return parent::throwException($sMessage, $mSender);
  }

  public function asArray() {

    $args = $this->getArguments();

    return $args->asArray();
  }

  public function __toString() {

    return $this->sPath;
  }
}




