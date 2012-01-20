<?php

namespace sylma\parser\action\php\basic;

class Controled {

  protected $controler;

  public function setControler(Window $controler) {

    $this->controler = $controler;
  }

  public function getControler() {

    return $this->controler;
  }
}