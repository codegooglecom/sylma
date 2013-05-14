<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\parser\languages\common;

class Select extends Basic implements common\argumentable {

  protected $aJoins = array();
  protected $sMethod = '';

  protected $offset;
  protected $count;
  protected $clone;

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

  public function setWhere($val1, $sOp, $val2, $sLog = 'AND') {

    if ($clone = $this->getClone()) {

      $clone->setWhere($val1, $sOp, $val2, $sLog);
    }

    return parent::setWhere($val1, $sOp, $val2, $sLog);
  }

  public function addJoin($table, $field, $val) {

    if ($clone = $this->getClone()) {

      $clone->addJoin($table, $field, $val);
    }

    $this->aJoins[] = array($table, $field, $val);
  }

  protected function getJoins() {

    $aResult = array();

    foreach ($this->aJoins as $iCurrent => $aJoin) {

      $aResult[] = array(' LEFT JOIN ', $aJoin[0], ' ON ', $aJoin[1], ' = ', $aJoin[2], ' ');
    }

    return $aResult;
  }

  public function setMethod($sMethod) {

    $this->sMethod = $sMethod;
  }

  protected function getMethod() {

    return $this->sMethod;
  }

  public function setOffset($offset) {

    $this->offset = $offset;
  }

  protected function getOffset() {

    return $this->offset;
  }

  public function setCount($count) {

    $this->count = $count;
  }

  protected function getCount() {

    return $this->count;
  }

  protected function getLimit() {

    if ($this->getOffset() && $this->getCount()) {

      $aResult = array(' LIMIT ', $this->getOffset(), ', ', $this->getCount());
    }
    else {

      $aResult = array();
    }

    return $aResult;
  }

  public function clearLimit() {

    $this->offset = $this->count = null;
  }

  protected function build($sMethod = '') {

    if (!$sMethod) {

      if (!$sMethod = $this->getMethod()) {

        $sMethod = $this->isMultiple() ? 'query' : 'get';
      }
    }

    parent::build($sMethod);
  }

  protected function getString() {

    $aQuery = array('SELECT ', $this->getColumns(), ' FROM ', $this->getTables(), $this->getJoins(), $this->getWheres(), $this->getLimit());

    return $this->getWindow()->createString($this->getWindow()->flattenArray($aQuery));
  }

  public function setClone(self $clone) {

    $this->clone = $clone;
  }

  protected function getClone() {

    return $this->clone;
  }
}

