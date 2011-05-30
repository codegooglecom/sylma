<?php

interface SylmaExceptionInterface {
  

}

class SylmaException extends Exception implements SylmaExceptionInterface {
	
	protected $aPath = array();
	protected $aCall = array();
	protected $bError = false;
	protected $iOffset = 1;
	
	public function loadException($iOffset = 1) {
		
    // for exceptions : line 1, file 2, class/method 2
    
		$aTrace = $this->getTrace();
		
    if (count($aTrace) < $iOffset + 2) {
      
      echo 'bad exception call'; // TODO
    }
    else {
    	
	    $aCall = $aTrace[$iOffset];
	    $aCaller = $aTrace[$iOffset + 1];
	    
	    $this->aCall = array(
	      'type' => 'method',
	      'value' => $aCaller['class'] . $aCaller['type'] . $aCaller['function']);
	    
	    $this->line = $aCall['line'];
	    $this->file = $aCaller['file'];
    }
    
    $this->save();
	}
	
	public function loadError($iNo, $sMessage, $sFile, $iLine) {
		
		$this->code = $iNo;
		$this->message = $sMessage;
		
		// for error : line def, file def, class/method 1
    
    $this->file = $sFile;
    $this->line = $iLine;
		
    $aTrace = $this->getTrace();
    
    if (count($aTrace) < 2) {
      
      echo 'bad exception call'; // TODO
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
	
	public function setPath(array $aPath) {
		
		$this->aPath = $aPath;
	}
  
	protected function getPath() {
		
		$sSystem = MAIN_DIRECTORY . '/' . SYLMA_PATH;
		
		$sCaller = array_val('type', $this->aCall, 'unknown');
		$sCall = array_val('value', $this->aCall, 'unknown');
		
		$aPath = array(
		  '@' . $sCaller => $sCall . '()',
      '@line' => $this->getLine(),
      '@file' => substr($this->getFile() ,strlen($sSystem) - 1),
      '@exception' => get_class($this) . ' [' . $this->getCode() . ']',
    );
    
		return array_merge($this->aPath, fusion(' ', $aPath));
	}
	
	public function save() {
		
		Sylma::log($this->getPath(), $this->getMessage());
	}
  
  /**
   * Associate properties of exceptions into a path with tokens
   */  
	public function __toString() {
  	
  	return  implode(' ', $this->getPath()) . ' @message ' . $this->getMessage() . '<br/>';
  }
}

