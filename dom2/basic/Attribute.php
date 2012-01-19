<?php

namespace sylma\dom\basic;
use \sylma\dom, \sylma\core;

require_once(dirname(__dir__) . '/attribute.php');

class Attribute extends \DOMAttr implements dom\attribute {
  
  public function __toString() {
    
    return $this->nodeValue;
  }
}

