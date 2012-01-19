<?php

namespace sylma\dom\argument;
use sylma\core, sylma\dom;

require_once('Basic.php');

class Iterator extends Basic {
  
  protected function getChildren() {
    
    return $this->getDocument()->getChildren();
  }

  public function rewind() {
    
    $this->getChildren()->rewind();
  }
  
  public function current() {
    
    return $this->getChildren()->current();
  }
  
  public function key() {
    
    return $this->getChildren()->key();
  }
  
  public function next() {
    
    $this->getChildren()->next();
  }
  
  public function valid() {
    
    return $this->getChildren()->valid();
  }
}
