<?php

interface SylmaExceptionInterface {
  

}

class SylmaException extends Exception implements SylmaExceptionInterface {
	
	protected $aPath = array();
	protected $aCall = array();
	protected static $bThrowError = false;
  
  /**
   * Allow import of other classes, used class is showed in message
   */
  protected $sClass = 'SylmaException';
	protected $iOffset = 1;
	
	public function load($iOffset = 1, $aTrace = array()) {
		
    // for exceptions : line 1, file 2, class/method 2
    
		$aTrace = $aTrace ? $aTrace : $this->getTrace();
		
    if (count($aTrace) < $iOffset + 2) {
      
      // echo 'bad exception call'; // TODO
    }
    else {
    	
	    $aCall = $aTrace[$iOffset];
	    $aCaller = $aTrace[$iOffset + 1];
	    
	    $this->aCall = array(
	      'type' => array_key_exists('class', $aCaller) ? 'method' : 'function',
	      'value' => array_val('class', $aCaller) . array_val('type', $aCaller) . array_val('function', $aCaller));
	    
	    $this->line = $aCall['line'];
	    $this->file = $aCall['file'];
    }
    
    $this->save();
	}
	
	public function setPath(array $aPath) {
		
		$this->aPath = $aPath;
	}
  
	protected function getPath() {
		
    if (Sylma::isWindows()) {
      
      $sSystem = $_SERVER['DOCUMENT_ROOT'] . '/' . MAIN_DIRECTORY;
      $sPath = pathWin2Unix(substr($this->getFile() ,strlen($sSystem)));
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
      '@exception' => $this->sClass . ' [' . $this->getCode() . ']',
    );
    
		return array_merge($this->aPath, fusion(' ', $aPath));
	}
	
  public static function loadError($iNo, $sMessage, $sFile, $iLine) {
  	
    if ($iNo & Sylma::read('users/root/error-level')) {
      
      $exception = new Sylma::$exception($sMessage);
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
  
	public function loadException(Exception $e) {
    
    $this->code = $e->getCode();
    $this->message = $e->getMessage();
    $this->file = $e->getFile();
    $this->line = $e->getLine();
    $this->sClass = get_class($e);
    
    $this->load(3, $e->getTrace());
  }
  
	public function importError($iNo, $sMessage, $sFile, $iLine) {
		
		$this->code = $iNo;
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
    
    $this->save();
	}
  
	public function save() {
		
		Sylma::log($this->getPath(), $this->getMessage(), 'error');
	}
  
  /**
   * Associate properties of exceptions into a path with tokens
   */  
	public function __toString() {
  	
  	return  implode(' ', $this->getPath()) . ' @message ' . $this->getMessage();
  }
}

