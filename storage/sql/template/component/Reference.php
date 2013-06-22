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

  protected function loadElementRef() {

    if ($table = parent::loadElementRef()) {

      list($sNamespace, $sName) = $this->parseName($this->readx('@foreign', true));
      $result = $table->getElement($sName, $sNamespace);
    }
    else {

      $result = null;
    }

    return $result;
  }

  protected function reflectFunctionRef(array $aPath, $sMode, array $aArguments = array()) {

    $element = $this->getElementRef();
    $table = $element->getParent();
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

  public function reflectApply($sMode) {
    ;
  }

}

