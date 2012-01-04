<?php

namespace sylma\parser\action\php\basic;

class BooleanInstance {
  
  private $bValue;
  
  public function __construct($bValue) {
    
    $this->bValue = $bValue;
  }
  
  public function parse() {
    
    require_once('core/functions/Text.php');
    
    return array(
      'boolean' => booltostr($this->bValue),
    );
  }
}    
