<?php

namespace sylma\parser\languages\common\basic;
use sylma\core, sylma\parser\languages\common;

class Returned extends Controled {

  protected $return;

  protected function setReturn(common\ghost $val) {

    $this->return = $val;
  }

  public function getReturn() {

    return $this->return;
  }
}

