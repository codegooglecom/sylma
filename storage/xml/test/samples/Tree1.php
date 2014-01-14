<?php

namespace sylma\storage\xml\test\samples;
use sylma\core, sylma\storage\xml;

class Tree1 extends xml\tree\Argument {

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    switch ($sName) {

      case 'test' : $result = 'abc'; break;

      default :

        $result = parent::reflectApplyFunction($sName, $aPath, $sMode);
    }

    return $result;
  }
}

