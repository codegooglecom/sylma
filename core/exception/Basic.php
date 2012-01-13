<?php

namespace sylma\core\exception;
use \sylma\core;

require_once('core/exception.php');
require_once('core/functions/Path.php');

class Basic extends \Exception implements core\exception {
  
  protected $aPath = array();
  protected $aCall = array();
  protected static $bThrowError = true;
  
  /**
   * Allow import of other classes, used class is showed in message
   */
  protected $iOffset = 1;
  
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
    
    $this->save();
  }
  
  public function setPath(array $aPath) {
    
    $this->aPath = $aPath;
  }
  
  protected function getPath() {
		
    if (\Sylma::isWindows()) {
      
      $sSystem = $_SERVER['DOCUMENT_ROOT'] . '/' . MAIN_DIRECTORY;
      $sPath = core\functions\path\winToUnix(substr($this->getFile() ,strlen($sSystem)));
    }
    else {
      
      $sSystem = MAIN_DIRECTORY . '/';
      $sPath = substr($this->getFile() ,strlen($sSystem) - 1);
    }

    $sCaller = array_val('type', $this->aCall, 'unknown');
    $sCall = array_val('value', $this->aCall, 'unknown');

    $aPath = array(
      '@' . $sCaller => $sCall . '()',
      '@line' => $this->getLine(),
      '@file' => $sPath,
      '@exception' => get_class($this) . ' [' . $this->getCode() . ']',
    );
    
		return array_merge($this->aPath, fusion(' ', $aPath));
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
    
    $this->save();
  }
  
  public function save() {
    
    \Sylma::log($this->getPath(), $this->getMessage(), 'error');
  }
  
  /**
   * Associate properties of exceptions into a path with tokens
   */  
  public function __toString() {
  	
  	return  implode(' ', $this->getPath()) . ' @message ' . $this->getMessage();
  }
}

