<?php

namespace sylma\parser\action\php\basic;
use \sylma\parser\action\php;

require_once('SimpleVar.php');
require_once(dirname(__dir__) . '/_object.php');

class ObjectVar extends SimpleVar implements php\_object {
  
  protected $object;
  
  public function __construct(ObjectInstance $object, $sName) {
    
    $this->setName($sName);
    $this->setObject($object);
  }
  
  public function getObject() {
    
    return $this->object;
  }
  
  public function setObject(ObjectInstance $object) {
    
    $this->object = $object;
  }

  public function asArgument() {
    
    return parent::asArgument();
  }
}