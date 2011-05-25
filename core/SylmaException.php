<?php

interface SylmaExceptionInterface {
  
  /**
   * Associate properties of exceptions into a path with tokens
   */
  public function getPath();
}

class SylmaException extends Exception implements SylmaExceptionInterface {
  
  public function getPath() {
    
    $aTrace = $this->getTrace();
    $aCall = $aTrace[1];
    
    $sSystem = Controler::getDirectory()->getSystemPath();
    
    $aPath = array(
      '@exception' => get_class($this) . ' [' . $this->getCode() . ']',
      '@class' => $aCall['class'] . $aCall['type'] . $aCall['function'] . '()',
      '@line' => $this->getLine(),
      '@file' => substr($this->getFile(), strlen($sSystem)),
    );
    
    return fusion(' ', $aPath);
  }
}

