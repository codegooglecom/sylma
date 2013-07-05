<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\parser\languages\common;

class Delete extends Select {

  protected $sMethod = 'read';

  public function getString() {

    $sTable = current($this->getTables());

    if (!$aWheres = $this->getWheres()) {

      $this->launchException('Cannot add DELETE query without WHERE clause');
    }

    $aQuery = array('DELETE FROM ', $sTable, $aWheres);

    return $this->getWindow()->createString($this->getWindow()->flattenArray($aQuery));
  }

  public function asArgument() {

    return $this->getCall()->getInsert();
  }
}

