<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\parser\languages\common;

class Select extends Basic implements common\argumentable, common\addable {

  protected $sMethod = '';
  protected $aJoins = array();

  protected $offset;
  protected $count;
  protected $clone;
  protected $order;

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

  public function setOrder($val) {

    $this->order = $val;
  }

  protected function getOrder() {

    return $this->order ? array(' ORDER BY `', $this->order, '` ASC ') : null;
  }

  public function onAdd() {

    $this->getWindow()->loadContent($this->aTables);
    $this->getWindow()->loadContent($this->aColumns);
    $this->getWindow()->loadContent($this->aWheres);
//$this->launchException('test');
    $this->getVar()->insert();
  }

  protected function build() {

    if (!$sMethod = $this->getMethod()) {

      $this->setMethod($this->isMultiple() ? 'query' : 'get');
    }

    parent::build();
  }

  protected function getString() {

    $aQuery = array('SELECT ', $this->getColumns(), ' FROM ', $this->getTables(), $this->getJoins(), $this->getWheres(), $this->getOrder(), $this->getLimit());

    return $this->getWindow()->createString($this->getWindow()->flattenArray($aQuery));
  }

  public function setClone(self $clone) {

    $this->clone = $clone;
  }

  protected function getClone() {

    return $this->clone;
  }
}

