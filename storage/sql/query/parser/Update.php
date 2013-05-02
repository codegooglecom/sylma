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

    if (!$aWheres = $this->getWheres()) {

      $this->launchException('Cannot build update query without where clause');
    }

    if (!$this->aSets) {

      $this->launchException('Cannot build update query without registered fields');
    }

    $aQuery = array('UPDATE ', $sTable, $this->getSets(), $aWheres);

    return $this->getWindow()->createString($this->getWindow()->flattenArray($aQuery));
  }

  protected function build($sMethod = 'read') {

    parent::build($sMethod);
  }
}

