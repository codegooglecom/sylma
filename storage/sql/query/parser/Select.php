<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\parser\reflector, sylma\parser\languages\common;

class Select extends reflector\component\Foreigner implements common\argumentable {

  protected $aColumns = array();
  protected $aTables = array();
  protected $aWheres = array();
  protected $aJoins = array();

  protected $var;
  protected $bMultiple = true;

  protected function implode($aArray, $sGlue = ', ') {

    $aResult = array();
    $iLast = count($aArray) - 1;
    $iCurrent = 0;

    foreach ($aArray as $mVal) {

      $aResult[] = $mVal;
      if ($iCurrent !== $iLast) $aResult[] = $sGlue;

      $iCurrent++;
    }

    return $aResult;
  }

  public function setColumn($val) {

    $this->aColumns[] = $val;
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

  protected function getColumns() {

    $aResult = array();

    if (!$this->aColumns) {

      $aResult[] = '*';
    }
    else {

      $aResult = $this->implode($this->aColumns);
    }

    return $aResult;
  }

  public function setTable($val) {

    $this->aTables[] = $val;
  }

  protected function getTables() {

    return $this->implode($this->aTables);
  }

  public function setWhere($val1, $sOp, $val2, $sLog = 'AND') {

    $this->aWheres[] = array(array($val1, $sOp, $val2), $sLog);
  }

  protected function getWheres() {

    $aResult = array();

    foreach ($this->aWheres as $iCurrent => $aWhere) {

      $aComp = array(' ', $aWhere[0][0], ' ', $aWhere[0][1], ' ', $aWhere[0][2], ' ');

      if ($iCurrent > 0) $aConcat = array($aWhere[1], $aComp);
      else $aConcat = $aComp;

      $aResult[] = $aConcat;
    }

    return $aResult ? array(' WHERE', $aResult) : null;
  }

  public function isMultiple($mValue = null) {

    if (!is_null($mValue)) $this->bMultiple = $mValue;
    return $this->bMultiple;
  }

  public function asArgument() {

    $aQuery = array('SELECT ', $this->getColumns(), ' FROM ', $this->getTables(), $this->getJoins(), $this->getWheres());

    return $this->getWindow()->createString($this->getWindow()->flattenArray($aQuery))->asArgument();
  }

  public function getVar() {

    $this->addTo();

    return $this->var;
  }

  protected function setVar(common\_object $var) {

    $this->var = $var;
  }

  public function addTo() {

    $window = $this->getWindow();

    if (!$this->var) {

      $manager = $window->addControler('mysql');
      $var = $window->addVar($window->createCall($manager, $this->isMultiple() ? 'query' : 'get', '\\sylma\\core\\argument', array($this)));

      $this->setVar($var);
    }
  }
}

