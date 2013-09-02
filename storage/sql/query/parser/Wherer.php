<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\parser\languages\common, sylma\schema, sylma\storage\sql;

abstract class Wherer extends Basic {

  protected $bDynamicWhere = true;
  protected $dynamicWhere;
  protected $aDynamicWhereCalls = array();

  public function setOptionalWhere($val1, $sOp, $val2, $sDefault = '', $sLog = 'AND') {

    $bImport = !$this->getDynamicWhere();
    $where = $this->loadDynamicWhere();

    if ($bImport) {

      foreach ($this->aWheres as $aWhere) {

        $aTest = $aWhere[0];
        $sSubLog = $aWhere[1];

        $this->addDynamicWhereCall($where->call('addStatic', array($this->getWindow()->toString($aTest), $sSubLog)));
      }

      $this->clearWheres();
    }

    $this->addDynamicWhereCall($where->call('add', array($val1, $sOp, $val2, $sDefault)));
  }

  protected function addDynamicWhereCall(common\_call $call) {

    $this->aDynamicWhereCalls[] = $this->getWindow()->createInstruction($call);
  }

  protected function getDynamicWhereCalls() {

    return $this->aDynamicWhereCalls;
  }

  protected function buildDynamicWhere() {

    $aResult[] = $this->getDynamicWhere()->getInsert();
    $aResult[] = $this->getDynamicWhereCalls();

    return $aResult;
  }

  protected function loadDynamicWhere() {

    if (!$this->getDynamicWhere()) {

      $where = $this->createObject('where', array($this->getConnection()));
      $this->setDynamicWhere($where);
    }

    return $this->getDynamicWhere();
  }

  protected function setDynamicWhere(common\_var $where) {

    $this->dynamicWhere = $where;
  }

  protected function getDynamicWhere() {

    return $this->dynamicWhere;
  }

  public function setWhere($val1, $sOp, $val2, $sLog = 'AND') {

    if ($where = $this->getDynamicWhere()) {

      $this->addDynamicWhereCall($where->call('addStatic', array($this->getWindow()->toString(array($val1, $sOp, $val2)))));
    }
    else {

      parent::setWhere($val1, $sOp, $val2, $sLog);
    }
  }

  protected function getWheres() {

    if (!$result = $this->getDynamicWhere()) {

      $result = parent::getWheres();
    }

    return $result;
  }
}

