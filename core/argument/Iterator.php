<?php

namespace sylma\core\argument;
use \sylma\core;

require_once('Basic.php');

class Iterator extends Basic {
  
  public function rewind() {
    
    reset($this->aArray);
  }
  
  public function current() {
    
    $sKey = key($this->aArray);
    
    return $this->get($sKey);
  }
  
  public function key() {
    
    return key($this->aArray);
  }
  
  public function next() {
    
    next($this->aArray);
  }
  
  public function valid() {
    
    return current($this->aArray) !== false;
  }
}
