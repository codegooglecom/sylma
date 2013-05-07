<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\parser\languages\common;

class Select extends Basic implements common\argumentable {

  protected $aJoins = array();
  protected $sMethod = '';

  public function setColumn($val) {

    $this->aColumns[] = $val;
  }

  protected function getColumns() {

    $aResult = array();

    if (!$this->aColumns) {

      $aResult[] = '*';
    }
    else {

      $aResult = parent::getColumns();
    }

    return $aResult;
  }

  public function clearColumns() {

    $this->aColumns = array();
  }

  public function addJoin($table, $field, $val) {

    $this->aJoins[] = array($table, $field, $val);
  }

  protected function getJoins() {

    $aResult = array();

    foreach ($this->aJoins as $iCurrent => $aJoin) {

      $aResult[] = array(' LEFT JOIN ', $aJoin[0], ' ON ', $aJoin[1], ' = ', $aJoin[2], ' ');
    }

    return $aResult;
  }

  protected function getString() {

    $aQuery = array('SELECT ', $this->getColumns(), ' FROM ', $this->getTables(), $this->getJoins(), $this->getWheres());

    return $this->getWindow()->createString($this->getWindow()->flattenArray($aQuery));
  }

  public function setMethod($sMethod) {

    $this->sMethod = $sMethod;
  }

  protected function getMethod() {

    return $this->sMethod;
  }

  protected function build($sMethod = '') {

    if (!$sMethod) {

      if (!$sMethod = $this->getMethod()) {

        $sMethod = $this->isMultiple() ? 'query' : 'get';
      }
    }

    parent::build($sMethod);
  }
}

