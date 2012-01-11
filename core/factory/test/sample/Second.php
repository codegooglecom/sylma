<?php

namespace sylma\core\factory\test\sample;

class Second {
  
  protected $sTest = '';

  public function __construct($sTest = '') {
    
    $this->sTest = $sTest;
  }
  
  public function getTest() {
    
    return $this->sTest;
  }
} 
