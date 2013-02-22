<?php

namespace sylma\core\exception;
use \sylma\core;

require_once('core/exception.php');
require_once('core/functions/Path.php');

class Basic extends \Exception implements core\exception {

  const NS_MESSAGE = 'http://www.sylma.org/core/message';
  /**
   * Number of exceptions created during the script
   * @var integer
   */
  protected static $iCount = 0;

  protected $aPath = array();
  protected $aCall = array();
  protected static $bThrowError = true;

  /**
   * Allow import of other classes, used class is showed in message
   */
  protected $iOffset = 1;

  public function __construct($message, $code = 0, $previous = null) {

    self::$iCount++;

    parent::__construct($message, $code, $previous);
  }

  public static function getCount() {

    return self::$iCount;
  }

  public function load($iOffset = 1, array $aTrace = array()) {

    // for exceptions : line 1, file 2, class/method 2

    //if ($aTrace) dspm(\Controler::getBacktrace($aTrace));
    $aTrace = $aTrace ? $aTrace : $this->getTrace();

    if (count($aTrace) < $iOffset + 2) {

      echo 'bad exception call'; // TODO
    }
    else {

      $aCall = $aTrace[$iOffset];
      $aCaller = $aTrace[$iOffset + 1];

      $this->aCall = array(
        'type' => array_key_exists('class', $aCaller) ? 'method' : 'function',
        'value' => self::loadKey('class', $aCaller) . self::loadKey('type', $aCaller) . self::loadKey('function', $aCaller));

      if (array_key_exists('line', $aCall)) $this->line = $aCall['line'];
      if (array_key_exists('file', $aCall)) $this->file = $aCall['file'];
    }

    //$this->save();
  }

  protected static function loadKey($sKey, array $aArray) {

    return array_key_exists($sKey, $aArray) ? $aArray[$sKey] : null;
  }

  public function addPath($sValue) {

    $this->aPath[] = $sValue;
  }

  public function setPath(array $aPath) {

    $this->aPath = $aPath;
  }

  protected function parsePath() {

    $aResult = $aPath = array();

    foreach ($this->aPath as $sPath) {

      $aResult[] = $this->parseString($sPath);
    }

    return $aResult;
  }

  protected function parseString($sValue) {

    $aResult = array();

    if ($fs = \Sylma::getControler('fs', false, false)) {

      if (preg_match_all('/@file [^\s]*/', $sValue, $aMatch, PREG_OFFSET_CAPTURE)) {

        $sFile = substr($aMatch[0][0][0], 6);
        $iFile = $aMatch[0][0][1];
        $iLength = strlen($sFile) + 6;

        $aResult[] = substr($sValue, 0, $iFile) . ' ';
        $aResult[] = '@file';

        $sDirectory = $fs->getDirectory()->getSystemPath();

        $aResult[] = '<a href="netbeans://' . $sDirectory . $sFile.'">' . $sFile . '</a>';
        $aResult[] = substr($sValue, $iFile + $iLength);
      }
      else {

        $aResult[] = $sValue;
      }
    }
    else {

      $aResult[] = $sValue;
    }

    return implode(' ', $aResult);
  }

  protected function getPath() {

    $sCall = self::loadKey('value', $this->aCall, 'unknown');

    //$link = new \HTML_A('netbeans://' . $this->getFile() . ':' . $this->getLine(), $sCall . '()');
    $link = '<a href="netbeans://' . $this->getFile() . ':' . $this->getLine() . '">' . $sCall . '()' . '</a>';
    //$message = $this->parseString($this->getMessage());
    $path = $this->parsePath();

    return array($link, $path);
  }

  public static function loadError($iNo, $sMessage, $sFile, $iLine) {

    //if ($iNo & \Sylma::read('users/root/error-level')) {

      $exception = new \Sylma::$sExceptionClass($sMessage);
      $exception->importError($iNo, $sMessage, $sFile, $iLine);

      if (self::throwError()) throw $exception;
    //}
  }

  /**
   * If set to TRUE, errors will be thrown as exceptions, else errors will only be logged and displayed to admin
   */
  public static function throwError($bThrow = null) {

    if ($bThrow !== null) self::$bThrowError = $bThrow;
    return self::$bThrowError;
  }

  public function loadException(\Exception $e) {

    $this->code = $e->getCode();
    $this->message = $e->getMessage();
    $this->file = $e->getFile();
    $this->line = $e->getLine();
    $this->sClass = get_class($e);

    $this->load(0, $e->getTrace());
  }

  public function importError($iNo, $sMessage, $sFile, $iLine) {

    $this->code = $iNo;
    //$this->message = checkEncoding($sMessage);
    $this->message = $sMessage;

    // for error : line def, file def, class/method 1

    $this->file = $sFile;
    $this->line = $iLine;

    $aTrace = $this->getTrace();

    if (count($aTrace) < 2) {

      // echo 'bad exception call'; // TODO
    }
    else {

      $aCall = $aTrace[1];

      $this->aCall = array(
        'type' => isset($aCall['class']) ? 'method' : 'function',
        'value' => isset($aCall['class']) ?
          $aCall['class'] . $aCall['type'] . $aCall['function'] :
          $aCall['function']);

    }

    //$this->save();
  }

  protected function strongLast($sValue, $sSep = '\\') {

    $sResult = $sValue;
    $iLast = strrpos($sValue, $sSep);

    if ($iLast !== false) {

      $sResult = substr($sValue, 0, $iLast + 1) . '<strong>' . substr($sValue, $iLast + 1) . '</strong>';
    }

    return $sResult;
  }

  public function errorAsHTML($iIndex, array $aError, $bHTML) {

    $sFile = array_key_exists('file', $aError) ? $aError['file'] : '-unknown-';
    $sLine = array_key_exists('line', $aError) ? $aError['line'] : '-unknown-';
    $sClass = array_key_exists('class', $aError) ? $aError['class'] : '-unknown-';
    $sFunction = array_key_exists('function', $aError) ? $aError['function'] : '-unknown-';

    $sDisplay = $this->strongLast(substr($sFile, strlen(\Sylma::PATH_SYSTEM)), '/');
    $sClass = $this->strongLast($sClass);

    $bHTML = $bHTML ? \Sylma::read('debug/backtrace/html') && !\Sylma::read('dom/debug/token') : false;

    if (array_key_exists('args', $aError)) { // TODO

      $sFunctionArgs = count($aError['args']);

      $sArgs = '<ul>';

      foreach ($aError['args'] as $arg) {

        $sArgs .= '<li>' . \Sylma::show($arg, !$bHTML) . '</li>';
      }

      $sArgs .= '</ul>';
    }
    else {

      $sArgs = '';
      $sFunctionArgs = 0;
    }

    return "<li tabindex=\"1\"><a href=\"netbeans://$sFile:$sLine\">$iIndex. $sDisplay [$sLine]</a> <span>$sClass-></span><span class=\"sylma-function\">$sFunction($sFunctionArgs)</span>$sArgs</li>";
  }

  public function save($bPrint = true, $bHTML = true) {

    $sResult = '';

    if (\Sylma::read('debug/enable')) {

      $aTraces = $this->getTrace();
      $aPath = $this->getPath();

      $sResult .= $this->parseString( htmlspecialchars($this->getMessage())) . '<br/>';
      $sResult .= $this->readPaths($aPath);
//echo $this->getMessage();
      if (\Sylma::read('debug/backtrace/show')) {

        $sResult .= '<ul class="sylma-backtrace">';

        foreach ($aTraces as $iTrace => $aTrace) {

          $sResult .= $this->errorAsHTML($iTrace, $aTrace, $bHTML);
        }

        $sResult .= '</ul>';
      }

      if ($bPrint) {

        echo $sResult;
      }
      else {

        \Sylma::display($sResult);
      }
    }

    return $sResult;
  }

  protected function readPaths(array $aPaths) {

    $sResult = '';

    foreach ($aPaths as $mPath) {

      if (is_array($mPath)) {

        $sResult .= $this->readPaths($mPath);
      }
      else if (trim($mPath)) {

        $sResult .= '<li>' .  $mPath . '</li>';
      }
    }

    return '<ul>' . $sResult .'</ul>';
  }

  protected function implodePath($aPath, $sSeparator = '') {

    $sResult = '';

    foreach ($aPath as $mKey => $mValue) {

      if (is_array($mValue)) $sValue = $sSeparator . $this->implodePath($mValue);
      else $sValue = $mValue;

      if (is_string($mKey)) $sKey = $mKey . ' ';
      else $sKey = '';

      $sResult .= $sKey . $sValue;
    }

    return $sResult;
  }

  /**
   * Associate properties of exceptions into a path with tokens
   */
  public function __toString() {

    return  $this->implodePath($this->getPath()) . ' @message ' . $this->getMessage();
  }
}

