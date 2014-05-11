<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\parser\languages\common, sylma\storage\sql;

class Insert extends Basic implements common\argumentable {

  protected $sMethod = 'insert';
  protected $aSets = array();
  protected $aValues = array();

  protected $dummy;

  public function setDummy(common\_var $handler) {

    $this->dummy = $handler;
  }

  protected function getDummy($bDebug = true) {

    if ($bDebug && !$this->dummy) {

      $this->launchException('No dummy defined');
    }

    return $this->dummy;
  }

  public function addSet($field, $val) {

    $this->aColumns[] = $field;
    $this->aValues[] = $val;
  }

  protected function getValues() {

    return $this->implode($this->aValues);
  }

  public function setTable(sql\template\component\Table $val) {

    if (count($this->aTables) > 1) {

      $this->launchException('Cannot load more than one table', get_defined_vars());
    }

    return parent::setTable($val);
  }

  public function getString() {

    $sTable = current($this->getTables());

    if ($this->getDummy(false)) {

      $aQuery = array('INSERT INTO ', $sTable, $this->getDummy()->call('asString'));
    }
    else {

      $aQuery = array('INSERT INTO ', $sTable, ' (', $this->getColumns() , ') VALUES (', $this->getValues(), ')');
    }

    return $this->getWindow()->createString($this->getWindow()->flattenArray($aQuery));
  }

  public function asArgument() {

    return $this->getHandler() || $this->getColumns() ? $this->getCall()->getInsert() : null;
  }
}

