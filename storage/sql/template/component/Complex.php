<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\storage\sql, sylma\template;

class Complex extends sql\schema\component\ComplexType implements template\parser\tree {

  protected $refElement;

  protected function setRefElement(parser\element $element) {

    $this->refElement = $element;
  }

  protected function getRefElement() {

    return $this->refElement;
  }

  protected function getQuery() {

    return $this->getParent()->getQuery();
  }

  protected function getVar() {

    return $this->getParent()->getVar();
  }

  public function reflectApplyPath(array $aPath, $sMode = '') {

    if (!$aPath) {

      $result = $this->reflectApplySelf($sMode);
    }
    else {

      $result = $this->parsePathToken($aPath, $sMode);
    }

    return $result;
  }

  public function reflectApply($sMode = '') {

    return $this->reflectRead();
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    switch ($sName) {

      case 'name' : $result = $this->getName(); break;

      default :

        $this->launchException('Uknown function', get_defined_vars());
    }

    return $result;
  }

  public function reflectApplyDefault($sPath, array $aPath, $sMode, $bRead = false, array $aArguments = array()) {

    $this->launchException('Not implemented');
  }

  protected function parsePathToken($aPath, $sMode) {

    return $this->getParser()->parsePathToken($this, $aPath, $sMode);
  }

  protected function lookupTemplate($sMode) {

    return $this->getParser()->lookupTemplate($this, 'type', $sMode);
  }

  protected function reflectApplySelf($sMode = '') {

    if ($result = $this->lookupTemplate($sMode)) {

      $result->setTree($this);
    }
    else {

      $result = $this->reflectRead();
    }

    return $result;
  }

  public function reflectRead() {

    $this->launchException('Cannot simply read type');
  }
}

