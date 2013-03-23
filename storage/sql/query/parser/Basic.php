<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\parser\reflector, sylma\parser\languages\common;

abstract class Basic extends reflector\component\Foreigner implements common\varable {

  protected $aColumns = array();
  protected $aTables = array();
  protected $aWheres = array();

  protected $var;

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

  protected function getColumns() {

    $aResult = array();

    if ($this->aColumns) {

      $aResult = $this->implode($this->aColumns);
    }

    return $aResult;
  }

  public function getVar() {

    $this->addTo();

    return $this->var;
  }

  protected function setVar(common\_object $var) {

    $this->var = $var;
  }

  protected function addToWindow($sMethod) {

    $window = $this->getWindow();

    if (!$this->var) {

      $manager = $window->addControler('mysql');
      $var = $window->addVar($window->createCall($manager, $sMethod, '\\sylma\\core\\argument', array($this)));

      $this->setVar($var);
    }
  }

  abstract protected function addTo();
}

