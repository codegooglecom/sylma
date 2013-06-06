<?php

namespace sylma\core\request;
use sylma\core, sylma\storage\fs, sylma\core\functions;

class Basic extends core\module\Filed implements core\request {

  const FILE_MANAGER = 'fs';

  private $sPath = '';

  protected $sExtension = '';
  protected $aExtensions = array();

  protected static $sArgumentClass = 'sylma\core\argument\Readable';

  const NS = 'http://www.sylma.org/parser/action/path';

  /**
   * @param string $sPath Path to look for an action
   * @param array $aArguments List of any arguments to add to the path
   * @param boolean $bParse Look for the correct file path through directories
   * @param $bArguments Use of indexed arguments (file/argument1/argument2)
   * @param $bDebug throw exceptions on error
   */
  public function __construct($sPath, fs\directory $dir = null, array $aArguments = array(), $bParse = true, core\argument $arg = null) {

    $sPath = $this->resolvePath($sPath, $dir);

    $this->setPath($sPath);
    $this->setNamespace(self::NS);

    //$this->setSettings($arg);
    $this->loadSettings();
    $this->setArguments($aArguments);

    //if (!$aExtensions) $aExtensions = $this->getManager('init')->getExtensions();
    //$this->setExtensions($aExtensions);

    if ($bParse) $this->parse();
  }

  protected function resolvePath($sPath, fs\directory $dir = null) {

    if ($dir) {

      require_once('core/functions/Path.php');
      $sResult = functions\path\toAbsolute((string) $sPath, $dir);
    }
    else {

      $sResult = (string) $sPath;
    }

    return $sResult;
  }

  protected function loadSettings() {

    $this->setSettings(include('arguments.xml.php'));
  }

  public function getFile($sPath = '', $bDebug = true) {

    return parent::getFile($sPath, $bDebug);
  }

  public function asFile($bDebug = false) {

    if (!$result = $this->getFile('', false)) {

      $this->setDirectory(__FILE__);

      if ($result = $this->getFile($this->getPath(), $bDebug)) {

        if (!in_array($result->getExtension(), $this->query('extensions/readable'))) {

          $this->throwException('Unauthorized extension type');
        }
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

  public function parse() {

    $file = null;
    $dir = $this->getManager(self::FILE_MANAGER)->getDirectory('/');
    $this->setExtensions($this->query('extensions/executable'));

    $aArguments = $this->getArguments()->query();

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

            if ($sSubPath) $sMessage = sprintf("No '%s' directory in %s", $sSubPath, $dir->asToken());
            else $sMessage = sprintf("No index file in %s", $dir->asToken());

            $this->launchException($sMessage, get_defined_vars());
          }
        }
        else {

          array_shift($aPath);
        }
      }

    } while (!$file && $sub);

    //$aPath = $this->loadIndexed($aPath);
    /*
    if ($aPath) {

      $sFile = $file ? $file->asToken() : '[no file found]';
      $this->throwException(sprintf('Too much arguments sent to %s', $sFile));
    }
    */

    $this->setFile($file);
    $this->setArguments($aPath, false);
    $this->getArguments()->mergeArray($aArguments);
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

  public function parseExtension($bRemove = true) {

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

  public function getArguments() {

    return parent::getArguments();
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




