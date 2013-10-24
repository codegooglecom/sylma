<?php

namespace sylma\storage\xml\test\samples;
use sylma\core, sylma\template;

class Tree1 extends template\parser\ArgumentTree {

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    switch ($sName) {

      case 'test' : $result = 'abc'; break;

      default :

        $result = parent::reflectApplyFunction($sName, $aPath, $sMode);
    }

    return $result;
  }
}

