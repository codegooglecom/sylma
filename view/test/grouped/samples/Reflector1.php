<?php

namespace sylma\view\test\grouped\samples;
use sylma\core, sylma\dom, sylma\template;

class Reflector1 extends template\parser\ArgumentTree {

  const NS = 'http://2013.sylma.org/view/parser/test/grouped/samples/reflector1';
  const NAME = 'sample1';

  public function parseRoot(dom\element $el = null) {

    $this->setNamespace(self::NS);
    $this->setName(self::NAME);
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    switch ($sName) {

      case 'test' :

        $result = 'Hello world !';
        break;

      default :

        $result = parent::reflectApplyFunction($sName, $aPath, $sMode, $bRead, $sArguments, $aArguments);
    }

    return $result;
  }
}

