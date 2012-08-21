<?php

namespace sylma\parser\languages\php\basic;
use sylma\parser\languages\common;

class Controled {

  protected $controler;

  public function setControler(common\_window $controler) {

    $this->controler = $controler;
  }

  public function getControler() {

    return $this->controler;
  }
}