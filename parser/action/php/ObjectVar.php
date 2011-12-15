<?php

namespace sylma\parser\action\php;

require_once('SimpleVar.php');
require_once('ObjectInterface.php');

class ObjectVar extends SimpleVar implements ObjectInterface {
  
  protected $object;

  public function __construct(ObjectInstance $object, $sName) {
    
    $this->setName($sName);
    $this->object = $object;
  }
  
  public function asArgument() {
    
    return parent::asArgument();
  }
}