<?php

namespace sylma\storage\sql\template\update;
use sylma\core, sylma\storage\sql;

class Reference extends sql\template\insert\Reference {

  protected function reflectID() {

    return $this->getParent()->getElementArgument('id');
  }

  protected function reflectFunctionRef(array $aPath, $sMode, array $aArguments = array()) {

    $el = $this->getElementRef();

    if (!$this->useID()) {

      $del = $this->loadSimpleComponent('template/delete');

      $del->setTable($el);
      $del->setConnection($this->getParent()->getConnection());
      $del->setWhere($this->getForeign(), '=', $this->reflectID());

      $this->getParent()->addTrigger(array($del));
    }

    parent::reflectFunctionRef($aPath, $sMode, $aArguments);
  }
}


