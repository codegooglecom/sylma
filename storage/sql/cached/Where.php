<?php

namespace sylma\storage\sql\cached;
use sylma\core, sylma\storage\sql;

class Where extends core\module\Managed {

  protected $db;
  protected $aValues = array();

  public function __construct(sql\Connection $db) {

    $this->db = $db;
  }

  public function add($val1, $sOp, $val2, $sDefault = null) {

    if ($val2) {

      $sql = $this->db;

      if (is_array($val2)) {

        $aNew = array();

        foreach ($val2 as $sSub) {

          $aNew[] = $sql->escape($sSub);
        }

        $val2 = '(' . implode(',', $aNew) . ')';
      }
      else {

        $val2 = $sql->escape($val2);
      }

      $this->addStatic($val1 . $sOp . $val2);
    }
    else if (!is_null($sDefault) && $sDefault !== '') {

      $val2 = is_array($val2) ? '(' . $sDefault . ')' : $sDefault;

      $this->addStatic($val1 . $sOp . $val2);
    }

  }

  public function addStatic($sValue) {

    $this->aValues[] = $sValue;
  }

  protected function getValues() {

    return $this->aValues;
  }

  public function __toString() {

    return $this->getValues() ? ' WHERE ' . implode(' AND ', $this->getValues()) : '';
  }
}

