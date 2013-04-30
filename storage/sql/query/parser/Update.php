<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\parser\languages\common;

class Update extends Insert {

  protected $aSets = array();

  public function addSet($field, $val) {

    $this->aSets[] = array($field, $val);
  }

  protected function getSets() {

    $aResult = array();

    foreach ($this->aSets as $aSet) {

      $aResult[] = array($aSet[0], ' = ', $aSet[1]);
    }

    return array(' SET ', $this->implode($aResult));
  }

  public function getString() {

    $sTable = current($this->getTables());

    $aQuery = array('UPDATE ', $sTable, $this->getSets(), $this->getWheres());

    return $this->getWindow()->createString($this->getWindow()->flattenArray($aQuery));
  }
}

