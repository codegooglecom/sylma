<?php

namespace sylma\dom\argument;
use \sylma\core, sylma\storage\fs;

require_once('Tokened.php');

class Filed extends Tokened {
  
  private $file = null;
  
  public function __construct(fs\file $file, array $aNS = array()) {
    
    $this->setFile($file);
    $doc = $file->getDocument();
    
    parent::__construct($doc, $aNS);
  }
  
  protected function getFile() {
    
    return $this->file;
  }

  protected function setFile(fs\file $file) {
    
    $this->file = $file;
  }
  
  protected function throwException($sMessage, $mSender = array(), $iOffset = 2) {
    
    $mSender[] = $this->getFile()->asToken();
    
    parent::throwException($sMessage, $mSender, $iOffset);
  }
}
