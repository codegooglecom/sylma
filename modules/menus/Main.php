<?php

namespace sylma\modules\menus;
use sylma\core, sylma\template, sylma\parser\languages\common;

class Main extends template\parser\ArgumentTree {

  protected $var;

  protected function build() {

    $this->setDirectory(__FILE__);
    $this->setArguments('main.xml');

    $result = $this->createObject();
    $this->setVar($result);
  }

  protected function setVar(common\_var $var) {

    $this->var = $var;
  }

  protected function getVar() {

    return $this->var;
  }

  public function reflectApply($sMode = '', array $aArguments = array()) {

    $aResult = array();

    if ($this->isRoot()) {

      $this->build();
      $aResult[] = $this->getVar()->getInsert();
    }

    $aResult[] = parent::reflectApply($sMode, $aArguments);

    return $aResult;
  }

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

        $result = parent::reflectApplyFunction($sName, $aPath, $sMode);
    }

    return $result;
  }
}

