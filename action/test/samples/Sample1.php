<?php

namespace sylma\action\test\samples;
use sylma\core;

class Sample1 {

  protected $a;
  protected $b;

  public function __construct($a, $b) {

    $this->a = $a;
    $this->b = $b;
  }

  public function __toString() {

    return $this->a . '-' . $this->b;
  }
}

