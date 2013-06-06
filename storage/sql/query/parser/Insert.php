<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\parser\languages\common;

class Insert extends Basic implements common\argumentable {

  protected $sMethod = 'insert';
  protected $aSets = array();
  protected $aValues = array();

  protected $handler;
/*
  public function addSet($field, $val) {

    $this->aColumns[] = $field;
    $this->aValues[] = $val;
  }
*/
  public function setHandler(common\_var $handler) {

    $this->handler = $handler;
  }

  protected function getHandler() {

    return $this->handler;
  }

  protected function getValues() {

    return $this->implode($this->aValues);
  }

  public function setTable($val) {

    if (count($this->aTables) > 1) {

      $this->launchException('Cannot load more than one table', get_defined_vars());
    }

    return parent::setTable($val);
  }

  public function getString() {

    $sTable = current($this->getTables());

    //$aQuery = array('INSERT INTO ', $sTable, ' (', $this->getColumns() , ') VALUES (', $this->getValues(), ')');
    $aQuery = array('INSERT INTO ', $sTable, $this->getHandler()->call('asString'));

    return $this->getWindow()->createString($this->getWindow()->flattenArray($aQuery));
  }

  public function _onAdd() {

    parent::onAdd();

    $this->getWindow()->loadContent($this->aSets);
    $this->getWindow()->loadContent($this->aValues);
  }
}

