<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\parser\languages\common;

class Update extends Insert {

  protected $aSets = array();
  protected $aValues = array();

  protected function getSets() {

    $aResult = array();

    foreach ($this->aSets as $aSet) {

      $aResult[] = array($aSet[0], ' = ', $aSet[1]);
    }

    return $aResult;
  }

  public function asArgument() {

    $sTable = current($this->getTables());

    $aQuery = array('UPDATE ', $sTable, ' (', $this->getColumns() , ') VALUES (', $this->getValues(), ')');

    return $this->getWindow()->createString($this->getWindow()->flattenArray($aQuery))->asArgument();
  }
}

