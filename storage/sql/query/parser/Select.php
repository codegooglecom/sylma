<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\parser\languages\common;

class Select extends Basic implements common\argumentable {

  protected $aJoins = array();
  protected $bMultiple = true;

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

  public function isMultiple($mValue = null) {

    if (!is_null($mValue)) $this->bMultiple = $mValue;
    return $this->bMultiple;
  }

  protected function getString() {

    $aQuery = array('SELECT ', $this->getColumns(), ' FROM ', $this->getTables(), $this->getJoins(), $this->getWheres());

    return $this->getWindow()->createString($this->getWindow()->flattenArray($aQuery));
  }

  protected function build($sMethod = '') {

    parent::build(($this->isMultiple() ? 'query' : 'get'));
  }
}

