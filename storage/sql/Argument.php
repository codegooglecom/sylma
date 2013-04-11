<?php

namespace sylma\storage\sql;
use sylma\core;

class Argument extends core\argument\Readable {

  function __construct($content, array $aNS = array(), core\argument $parent = null) {

    $this->setNamespaces($aNS);
    if ($parent) $this->setParent($parent);

    $this->aArray = $this->loadContent($content);
  }

  protected function loadContent($val) {

    if ($val instanceof \PDOStatement) {

      $aResult = array();

      foreach ($val as $sKey => $row) {

        $aResult[$sKey] = $row;
      }
    }
    else {

      $aResult = $val;
    }

    return $aResult;
  }

  public function read($sPath = '', $bDebug = true) {

    //return $this->aArray[$sPath];

    return parent::read($sPath, $bDebug);
  }
}
