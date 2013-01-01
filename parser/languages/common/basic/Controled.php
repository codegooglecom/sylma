<?php

namespace sylma\parser\languages\common\basic;
use sylma\parser\languages\common;

class Controled {

  protected $controler;
  protected $var;

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

  /**
   * Build an (optionnaly temporary) variable assigned with this call
   * @param boolean $bInsert
   * @return common\_var
   */
  public function getVar($bInsert = true) {

    if (!$this->var) {

      $this->var = $this->getControler()->createVar($this);
    }

    if ($bInsert) $this->var->insert();

    return $this->var;
  }

}