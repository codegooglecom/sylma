<?php

namespace sylma\storage\sql;
use sylma\core;

class Argument extends core\argument\Readable {

  function __construct($content, array $aNS = array(), core\argument $parent = null) {

    $this->setNamespaces($aNS);
    if ($parent) $this->setParent($parent);

    $this->aArray = $content;
  }

  public function read($sPath = '', $bDebug = true) {

    //return $this->aArray[$sPath];

    return parent::read($sPath, $bDebug);
  }
}

