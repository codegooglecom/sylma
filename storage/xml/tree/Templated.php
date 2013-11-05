<?php

namespace sylma\storage\xml\tree;
use sylma\core, sylma\dom;

class Templated extends Argument {

  const JS_NS = 'http://2013.sylma.org/template/binder';

  public function reflectApplyDefault($sPath, array $aPath = array(), $sMode = '', $bRead = false) {

    if ($bRead && $this->getRoot()->getMode() == 'script') {

      $parser = $this->getParser()->getParser(self::JS_NS);
      $result = $parser->getSource()->getProperty($sPath);
    }
    else {

      $result = parent::reflectApplyDefault($sPath, $aPath, $sMode, $bRead);
    }

    return $result;
  }
}

