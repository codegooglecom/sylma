<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\parser\languages\common, sylma\storage\sql;

abstract class Wherer extends Basic {

  protected $bDynamicWhere = true;
  protected $dynamicWhere;
  protected $aDynamicWhereCalls = array();

  protected function getCollation($sCharset) {

    switch ($sCharset) {

      case 'latin1' :

        $sResult = 'latin1_general_ci';
        break;

      case 'utf8' :
      default :

        $sResult = 'utf8_general_ci';
    }

    return $sResult;
  }

  public function setOptionalWhere($val1, $sOp, $val2, $sDefault = '', $sLog = 'AND') {

    $bImport = !$this->getDynamicWhere();
    $where = $this->loadDynamicWhere();

    if ($bImport) {

      foreach ($this->aWheres as $aWhere) {

        $aTest = array($aWhere[0][0], ' ', $aWhere[0][1], ' ', $aWhere[0][2]);
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

    $aResult = array();

    if ($this->getDynamicWhere()) {

      $aResult[] = $this->getDynamicWhere()->getInsert();
      $aResult[] = $this->getDynamicWhereCalls();
    }

    return $aResult;
  }

  protected function loadDynamicWhere() {

    if (!$this->getDynamicWhere()) {

      $where = $this->createObject('where', array($this->getConnection(), $this->getCollation($this->getCharset())));
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

      $this->addDynamicWhereCall($where->call('addStatic', array($this->getWindow()->toString(array($val1, ' ', $sOp, ' ', $val2)))));
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

