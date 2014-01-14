<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\parser\reflector, sylma\parser\languages\common, sylma\storage\sql;

abstract class Basic extends reflector\component\Foreigner implements common\instruction {

  protected $sMethod = 'get';
  protected $bMultiple = false;
  protected $bOptional = false;

  protected $aColumns = array();
  protected $aTables = array();
  protected $aWheres = array();

  protected $connection;
  protected $aMethods = array('get', 'query', 'insert', 'extract');

  protected $handler;
  protected $var;

  public function __construct(reflector\domed $parser, core\argument $arg = null, array $aNamespaces = array()) {

    parent::__construct($parser, $arg, $aNamespaces);

    $this->getWindow()->addControler('mysql');
  }

  public function setConnection(common\_callable $var) {

    $this->connection = $var;
  }

  protected function getConnection() {

    if (!$this->connection) {

      $this->launchException('No connection defined');
    }

    return $this->connection;
  }

  public function setHandler(common\_var $handler) {

    $this->handler = $handler;
  }

  protected function getHandler() {

    return $this->handler;
  }

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

  public function setTable(sql\template\component\Table $table) {

    if (!$this->connection) {

      $this->setConnection($table->getConnection());
    }

    $this->aTables[] = $table;
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

  protected function clearWheres() {

    $this->aWheres = array();
  }

  public function setColumn($val) {

    $this->aColumns[] = $val;
  }

  protected function getColumns() {

    $aResult = array();

    if ($this->aColumns) {

      $aResult = $this->implode($this->aColumns);
    }

    return $aResult;
  }

  public function getCall($bDebug = true) {

    if (!$this->getTables()) {

      $this->launchException('No table defined');
    }

    $self = $this;
    $caller = $this->getWindow()->createCaller(function() use ($self) {
      return $self->getString();
    });

    return $this->getConnection()->call($this->getMethod(), array($caller, $bDebug), '\sylma\core\argument');
  }

  public function setMethod($sName) {

    if (!in_array($sName, $this->aMethods)) {

      $this->launchException("Unknown method name : $sName");
    }

    $this->sMethod = $sName;
  }

  protected function getMethod() {

    return $this->sMethod;
  }

  public function getVar() {

    if (!$this->var) {

      $this->build();
    }

    return $this->var;
  }

  protected function setVar(common\_object $var) {

    $this->var = $var;
  }

  protected function build() {

    $this->setVar($this->getWindow()->createVar($this->getCall()));
  }

  public function isMultiple($mValue = null) {

    if (!is_null($mValue)) $this->bMultiple = $mValue;
    return $this->bMultiple;
  }

  public function isOptional($mValue = null) {

    if (!is_null($mValue)) $this->bOptional = $mValue;
    return $this->bOptional;
  }

  abstract public function getString();

  public function __clone() {

    $this->var = null;
  }
}

