<?php

namespace sylma\modules\menus;
use sylma\core, sylma\storage\xml;

class Main extends xml\tree\Argument {

  protected function loadChild(core\argument $content, $iPosition = null) {

    $result = parent::loadChild($content);
    $result->setDummy($this->getDummy());

    return $result;
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    switch ($sName) {

      case 'check-active' :

        $result = $this->getDummy()->call('checkActive', array($this->reflectApplyDefault('@href', array(), '', true)));

        break;


      default :

        $result = parent::reflectApplyFunction($sName, $aPath, $sMode, $bRead, $sArguments,$aArguments);
    }

    return $result;
  }
}

