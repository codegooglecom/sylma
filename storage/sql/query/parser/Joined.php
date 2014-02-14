<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\storage\sql;

abstract class Joined extends Wherer {

  protected $aJoins = array();
  protected $aJoinsElements = array();

  public function addJoin(sql\schema\table $table, sql\schema\element $field, $val) {

    $bAdd = true;

    foreach ($this->aJoinsElements as $el) {

      if ($el === $field) {

        $bAdd = false;
      }
    }

    if ($bAdd) {

      foreach($this->getClones() as $clone) {

        $clone->addJoin($table, $field, $val);
      }

      $this->aJoins[] = array($table, $field, $val);
      $this->aJoinsElements[] = $field;
    }
  }

  protected function getJoins() {

    $aResult = array();

    foreach ($this->aJoins as $iCurrent => $aJoin) {

      $aResult[] = array(' LEFT JOIN ', $aJoin[0], ' ON ', $aJoin[1], ' = ', $aJoin[2], ' ');
    }

    return $aResult;
  }

  public function clearJoins() {

    $this->aJoins = array();
  }
}
