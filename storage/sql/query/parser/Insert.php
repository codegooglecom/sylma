<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\parser\languages\common;

class Insert extends Basic implements common\argumentable {

  protected $aSets = array();
  protected $aValues = array();

  public function addSet($field, $val) {

    $this->aColumns[] = $field;
    $this->aValues[] = $val;
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

  public function asArgument() {

    $sTable = current($this->getTables());

    $aQuery = array('INSERT INTO ', $sTable, ' (', $this->getColumns() , ') VALUES (', $this->getValues(), ')');

    return $this->getWindow()->createString($this->getWindow()->flattenArray($aQuery))->asArgument();
  }

  public function addTo() {

    return $this->addToWindow('get');
  }
}

