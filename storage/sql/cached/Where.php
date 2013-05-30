<?php

namespace sylma\storage\sql\cached;
use sylma\core;

class Where extends core\module\Managed {

  protected $aValues = array();

  public function add($val1, $sOp, $val2) {

    if ($val2) {

      $sql = $this->getManager('mysql');
      $this->addStatic($val1 . $sOp . $sql->escape($val2));
    }
  }

  public function addStatic($sValue) {

    $this->aValues[] = $sValue;
  }

  protected function getValues() {

    return $this->aValues;
  }

  public function __toString() {

    return $this->getValues() ? ' WHERE ' . implode($this->getValues()) : '';
  }
}

