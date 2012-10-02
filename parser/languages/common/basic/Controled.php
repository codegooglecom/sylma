<?php

namespace sylma\parser\languages\common\basic;
use sylma\parser\languages\common;

class Controled {

  protected $controler;

  public function setControler(common\_window $controler) {

    $this->controler = $controler;
  }

  /**
   *
   * @return window
   */
  public function getControler() {

    return $this->controler;
  }
}