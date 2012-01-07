<?php

namespace sylma\parser\action\php;

class ArrayInstance {
  
  private $aValues;
  
  public function __construct(array $aValues = array()) {
    
    $this->aValues = $aValues;
  }
  
  public function parse() {
    
    return array(
      'array' => implode('', $this->aValues),
    );
  }
}    
