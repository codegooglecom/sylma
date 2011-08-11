<?php

namespace sylma\parser\action;
use sylma\dom, sylma\parser;

class Basic implements parser\action {
  
  private $doc;
  
  public function __construct(dom\document $doc) {
    
    $this->doc = $doc;
  }
  
  public function parse() {
    
    dspf('parsed');
  }
}

namespace sylma\parser;

interface action {

}
