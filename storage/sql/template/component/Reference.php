<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common, sylma\schema\parser;

class Reference extends sql\schema\component\Reference implements sql\template\pathable, parser\element {

  protected $query;
  protected $var;

  protected $bBuilded = false;

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

  protected function loadForeign() {

    list($sNamespace, $sName) = $this->parseName($this->readx('@foreign', true));
    return $this->getElementRef()->getElement($sName, $sNamespace);
  }

  protected function reflectFunctionRef(array $aPath, $sMode, array $aArguments = array()) {

    $table = $this->getElementRef();
    $element = $this->loadForeign();

    $collection = $this->loadSimpleComponent('component/collection');

    $collection->setTable($table);

    if ($element->getMaxOccurs(true)) {

      $this->launchException('Not implemented');
    }
    else {

      $table->getQuery()->setWhere($element, '=', $this->getParent()->getElement('id')->reflectRead());
    }

    if ($aPath) {

      $result = $this->getParser()->parsePathToken($collection, $aPath, $sMode);
    }
    else {

      $result = $collection->reflectApplyAll($sMode, $aArguments);
    }

    return $result;
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

