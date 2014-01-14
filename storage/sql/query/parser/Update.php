<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\parser\languages\common;

class Update extends Insert {

  protected $sMethod = 'read';

  public function addSet($field, $val) {

    $this->aSets[] = array($field, $val);
  }

  public function getString() {

    $sTable = current($this->getTables());

    if (!$aWheres = $this->getWheres()) {

      $this->launchException('Cannot build update query without where clause', get_defined_vars());
    }
/*
    if (!$this->aSets) {

      $this->launchException('Cannot build update query without registered fields');
    }
*/
    $aQuery = array('UPDATE ', $sTable, ' SET ', $this->getHandler()->call('asString'), $aWheres);

    return $this->getWindow()->createString($this->getWindow()->flattenArray($aQuery));
  }
}

