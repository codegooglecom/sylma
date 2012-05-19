<?php

namespace sylma\parser\action\php\basic;
use sylma\parser\action\php;

class Controled {

  protected $controler;

  public function setControler(php\_window $controler) {

    $this->controler = $controler;
  }

  public function getControler() {

    return $this->controler;
  }
}