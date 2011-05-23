<?php

class Any implements WindowInterface {
  
  private $oFile = null;
  
  public function loadAction($oFile) {
    
    $this->oFile = $oFile;
  }
  
  public function __toString() {
    
    if ($this->oFile) {
      
      $sPath = MAIN_DIRECTORY.'/'.$this->oFile;
      
      Controler::setContentType($this->oFile->getExtension());
      header('Content-Length: ' . $this->oFile->getSize());
      header('Content-Disposition: attachment; filename=' . basename($sPath));
      // header('Content-Description: File Transfer');
      readfile($sPath);
    }
    
    return '';
  }
}

