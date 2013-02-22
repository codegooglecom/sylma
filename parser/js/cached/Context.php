<?php

namespace sylma\parser\js\cached;
use sylma\core, sylma\parser\context;

class Context extends context\Basic {

  public function __construct() {

    //$this->setControler()
  }

  public function set($sPath, $mValue) {


  }

  public function asString() {

    $aResult = $this->getArguments()->asArray();

    return (string) implode('', $aResult);
  }
}
