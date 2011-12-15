<?php

namespace sylma\parser\action\php;

require_once('ObjectInterface.php');

class ObjectInstance implements ObjectInterface {
  
  private $sInterface = '';
  
  public function __construct($sInterface) {
    
    $this->sInterface = $sInterface;
  }
}