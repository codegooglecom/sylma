<?php

namespace sylma\core\exception;
use \sylma\core;

require_once('core/exception.php');
require_once('core/functions/Path.php');

class Basic extends \Exception implements core\exception {

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

  public function load($iOffset = 1, $aTrace = array()) {

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
        'value' => array_val('class', $aCaller) . array_val('type', $aCaller) . array_val('function', $aCaller));

      if (array_key_exists('line', $aCall)) $this->line = $aCall['line'];
      if (array_key_exists('file', $aCall)) $this->file = $aCall['file'];
    }

    //$this->save();
  }

  public function setPath(array $aPath) {

    $this->aPath = $aPath;
  }

  protected function parsePath() {

    $aResult = $aPath = array();

    foreach ($this->aPath as $sPath) {

      $aResult[] = ' ';
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

        $aResult[] = new \HTML_A('netbeans://' . $sDirectory . $sFile, $sFile);
        $aResult[] = substr($sValue, $iFile + $iLength);
      }
      else {

        $aResult[] = $sValue;
      }
    }
    else {

      $aResult[] = $sValue;
    }

    return $aResult;
  }

  protected function getPath() {

    $sCall = array_val('value', $this->aCall, 'unknown');

    $link = new \HTML_A('netbeans://' . $this->getFile() . ':' . $this->getLine(), $sCall . '()');
    $message = $this->parseString($this->getMessage());
    $path = $this->parsePath();

		return array($link, $path, ' ', $message);
	}

  public static function loadError($iNo, $sMessage, $sFile, $iLine) {

    if ($iNo & \Sylma::read('users/root/error-level')) {

      $exception = new \Sylma::$exception($sMessage);
      $exception->importError($iNo, $sMessage, $sFile, $iLine);

      if (self::throwError()) throw $exception;
    }
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
    $this->message = checkEncoding($sMessage);

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

  public function save() {

    if (\Controler::useMessages()) {

      if (\Sylma::read('messages/print/visible')) {

        print_r($this->getPath());
      }
      $backtrace = \Controler::getBacktrace($this->getTrace());
      \Controler::addMessage(array($this->getPath(), $backtrace));
    }
  }

  /**
   * Associate properties of exceptions into a path with tokens
   */
  public function __toString() {

  	return  implode(' ', $this->getPath()) . ' @message ' . $this->getMessage();
  }
}

