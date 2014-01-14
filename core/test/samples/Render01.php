<?php

namespace sylma\core\test\samples;
use sylma\core;

class Render01 extends core\module\Domed implements core\stringable {

  public function __construct($sValue) {

    2/0;
  }

  public function asString() {

    //return $this->sValue;
  }
}

