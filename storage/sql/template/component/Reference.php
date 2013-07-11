<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common, sylma\schema\parser;

class Reference extends sql\schema\component\Reference implements sql\template\pathable, parser\element {

  protected $query;
  protected $var;
  protected $foreign;

  protected $bBuilded = false;

  protected function importElementRef() {

    $result = parent::importElementRef();

    if ($result) {

      $result->isSub(true);
    }

    return $result;
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    switch ($sName) {

      case 'alias' : $result = $this->getAlias(); break;
      //case 'all' : $result = $this->reflectFunctionAll($aPath, $sMode, $aArguments); break;
      case 'ref' : $result = $this->reflectFunctionRef($aPath, $sMode, $aArguments); break;
      case 'title' : $result = $this->getTitle(); break;

      default :

        $this->launchException("Invalid function name : '{$sName}'");
        //$result = $this->getParser()->parsePathFunction($this, $aMatch, $aPath, $sMode);
    }

    return $result;
  }

  protected function getForeign() {

    if (is_null($this->foreign)) {

      $result = $this->loadForeign();
      $this->foreign = $result ? $result : false;
    }

    return $this->foreign;
  }

  protected function loadForeign() {

    list($sNamespace, $sName) = $this->parseName($this->readx('@foreign', true));
    return $this->getElementRef()->getElement($sName, $sNamespace);
  }

  protected function reflectFunctionRef(array $aPath, $sMode, array $aArguments = array()) {

    $table = $this->getElementRef();

    return $this->getParser()->parsePathToken($table, $aPath, $sMode, $aArguments);
  }

  public function reflectApply($sMode = '', array $aArguments = array()) {

    return $this->reflectApplySelf($sMode, $aArguments);
  }

  public function reflectRead() {

    return null;
  }

  protected function lookupTemplate($sMode) {

    return $this->getParser()->lookupTemplate($this, 'element', $sMode);
  }

  protected function reflectApplySelf($sMode, array $aArguments = array()) {

    if ($result = $this->lookupTemplate($sMode)) {

      $result->setTree($this);
      $result->sendArguments($aArguments);
    }
    else {

      $result = null;
    }

    return $result;
  }
}

