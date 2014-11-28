<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\storage\sql, sylma\storage\sql\query;

class Reference extends sql\template\component\Reference {

  protected function reflectID() {

    return $this->getParent()->getResult();
  }

  protected function importElementRef() {

    $this->getParser()->changeMode($this->useID() ? 'update' : 'insert');
    //$this->getParser()->changeMode($this->useID() ? $this->getParent()->getMode() : 'insert');
    $result = parent::importElementRef();
    $this->getParser()->resetMode();

    return $result;
  }

  protected function reflectFunctionRef(array $aPath, $sMode, array $aArguments = array()) {

    $result = $this->getParent()->loadMultipleReference($this->getName(), $this->getElementRef(), $aPath, $sMode, $aArguments, $this->getForeign(), $this->reflectID());

    return $result;
  }
}

