<?php

namespace sylma\storage\sql\template\view;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common, sylma\schema\parser;

class Reference extends sql\template\component\Reference {

  protected function reflectFunctionRef(array $aPath, $sMode, array $aArguments = array()) {

    $table = $this->getElementRef();
    $element = $this->getForeign();

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
}

