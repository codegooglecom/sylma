<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\parser\reflector, sylma\parser\languages\common;

class Select extends reflector\component\Foreigner implements common\argumentable {

  protected $aColumns = array();
  protected $aTables = array();
  protected $aWheres = array();

  protected function implode($aArray, $sGlue = ', ', $bQuote = true) {

    $aResult = array();
    $iLast = count($aArray) - 1;
    $iCurrent = 0;

    foreach ($aArray as $mVal) {

      if ($bQuote) $aResult[] = '`';
      $aResult[] = $mVal;
      if ($bQuote) $aResult[] = '`';
      if ($iCurrent !== $iLast) $aResult[] = $sGlue;

      $iCurrent++;
    }

    return $aResult;
  }

  public function setColumn($val) {

    $this->aColumns[] = $val;
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

  public function asArgument() {

    $aQuery = array('SELECT ', $this->getColumns(), ' FROM ', $this->getTables(), $this->getWheres());

    return $this->getWindow()->createString($aQuery)->asArgument();
  }
}

