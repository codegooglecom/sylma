<?php

require_once('TestInterface.php');

class Test extends Module implements TestInterface {
  
  const NS = 'http://www.sylma.org/modules/test';
  
  public function __construct() {
    
    $this->setNamespace(self::NS, 'test');
  }
}