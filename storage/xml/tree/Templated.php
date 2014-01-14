<?php

namespace sylma\storage\xml\tree;
use sylma\core, sylma\dom;

class Templated extends Argument {

  const JS_NS = 'http://2013.sylma.org/template/binder';

  protected function useScript() {

    return $this->getRoot()->getMode() == 'script';
  }

  protected function loadProperty($sName) {

    $parser = $this->getParser()->getParser(self::JS_NS);
    return $parser->getSource()->getProperty($sName);
  }

  public function reflectRead(array $aArguments = array()) {

    if ($this->useScript()) {

      $result = $this->loadProperty('value');
    }
    else {

      $result = parent::reflectRead($aArguments);
    }

    return $result;
  }

  public function reflectApplyDefault($sPath, array $aPath = array(), $sMode = '', $bRead = false) {

    if ($bRead && $this->useScript()) {

      $sName = str_replace('@', '', $sPath);
      $result = $this->loadProperty($sName);
    }
    else {

      $result = parent::reflectApplyDefault($sPath, $aPath, $sMode, $bRead);
    }

    return $result;
  }
}

