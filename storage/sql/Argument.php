<?php

namespace sylma\storage\sql;
use sylma\core;

class Argument extends core\argument\Readable {

  function __construct(array $content = array(), array $aNS = array(), core\argument $parent = null) {

    $this->setNamespaces($aNS);
    if ($parent) $this->setParent($parent);

    $this->setArray($content);
  }

  public function read($sPath = '', $bDebug = true) {

    $sResult = parent::read($sPath, $bDebug);

    return htmlspecialchars($sResult);
  }
}

