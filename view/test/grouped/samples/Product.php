<?php

namespace sylma\view\test\grouped\samples;
use sylma\core, sylma\schema\cached\form;

class Product extends core\module\Domed {

  protected $sum = 0;

  public function add($val) {

    $this->sum += $val;
  }

  public function sum() {

    return $this->sum;
  }
}

