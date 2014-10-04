<?php

namespace sylma\storage\xml\test\samples;
use sylma\core;

class Cached01 extends core\module\Argumented {

  public function getPaths($sValue = '') {

    if (!$sValue) {

      $sValue = '/abc';
    }
    
    return implode(',', array($sValue, '/def'));
  }
}

