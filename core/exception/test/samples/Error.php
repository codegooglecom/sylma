<?php

namespace sylma\core\exception\test\samples;
use sylma\core;

class Error extends core\module\Domed implements core\stringable {

  public function __construct($sValue) {

    2/0;
  }

  public function asString() {

    //return $this->sValue;
  }
}

