<?php

namespace sylma\parser\js\cached;
use sylma\core, sylma\parser, sylma\dom;

\Sylma::load('../../context/Basic.php', __DIR__);

class Context extends parser\context\Basic {

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
