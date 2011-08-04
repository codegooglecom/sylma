<?php

namespace sylma\parser\action;
use sylma\dom;

class cache {
  
  public function __construct($sPath) {
    
    $doc = new dom\document($sPath);
    
    dspf($doc);
  }
}