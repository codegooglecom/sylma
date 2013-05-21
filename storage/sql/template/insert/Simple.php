<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common;

class Simple extends sql\template\component\Simple {

  protected $var;

  public function instanciate($val, $aSettings) {

    return $this->createObject('cached', array($val, $aSettings), null, false);
  }
}

