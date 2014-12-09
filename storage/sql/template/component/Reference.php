<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common, sylma\schema\parser;

class Reference extends sql\schema\component\Reference implements sql\template\pathable, parser\element {

  protected $query;
  protected $var;
  protected $foreign;

  protected $bBuilded = false;

  protected function importElementRef() {

    if ($result = parent::importElementRef()) {

      $result->isSub(true);
    }

    return $result;
  }

  protected function useID() {

    return $this->readx('@use-id');
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    switch ($sName) {

      case 'alias' : $result = $this->getAlias(); break;
      case 'parent' : $result = $this->getHandler()->parsePathToken($this->getParent(), $aPath, $sMode, $bRead, $aArguments); break;
      case 'foreign' : $result = $this->getHandler()->parsePathToken($this->getForeign(), $aPath, $sMode, $bRead, $aArguments); break;
      //case 'all' : $result = $this->reflectFunctionAll($aPath, $sMode, $aArguments); break;
      case 'ref' : $result = $this->reflectFunctionRef($aPath, $sMode, $aArguments); break;
      case 'title' : $result = $this->getTitle(); break;
      case 'static' : $result = $this->reflectStatic($aPath, $sMode); break;
      case 'use-id' : $result = $this->useID(); break;

      default :

        $this->launchException("Invalid function name : '{$sName}'");
        //$result = $this->getParser()->parsePathFunction($this, $aMatch, $aPath, $sMode);
    }

    return $result;
  }

  /**
   * @return sql\schema\foreign
   */
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

    return $this->getElementRef()->reflectApply($sMode, $aPath, true);
  }

  public function reflectApply($sMode = '', array $aArguments = array()) {

    return $this->reflectApplySelf($sMode, $aArguments);
  }

  protected function reflectStatic(array $aPath, $sMode) {

    return $this->getElementRef()->reflectApply($sMode, $aPath, true);
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

  public function reflectRegister() {

    $this->launchException('Cannot register reference');
  }

  public function reflectApplyAll() {

    $this->launchException('Should not be called');
  }

  public function reflectApplyDefault($sPath, array $aPath = array(), $sMode = '', $bRead = false, array $aArguments = array()) {

    $this->launchException('Should not be called');
  }
}

