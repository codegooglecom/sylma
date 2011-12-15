<?php

namespace sylma\dom\basic;
use \sylma\dom;

require_once(dirname(__dir__) . '/text.php');

class Text extends \DOMText implements dom\text {
  
  public function getDocument() {
    
    return $this->ownerDocument;
  }
  

}
