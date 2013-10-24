<?php

namespace sylma\modules\menus;
use sylma\core, sylma\template;

class Main extends template\parser\ArgumentTree {

  protected function loadChild(core\argument $content) {

    $result = parent::loadChild($content);
    $result->setVar($this->getVar());

    return $result;
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    switch ($sName) {

      case 'check-active' :

        $result = $this->getVar()->call('checkActive', array($this->reflectApplyDefault('@href', array(), '', true)));

        break;


      default :

        $result = parent::reflectApplyFunction($sName, $aPath, $sMode, $bRead, $sArguments,$aArguments);
    }

    return $result;
  }
}

