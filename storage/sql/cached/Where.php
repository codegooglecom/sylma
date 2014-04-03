<?php

namespace sylma\storage\sql\cached;
use sylma\core, sylma\storage\sql;

class Where extends core\module\Managed {

  protected $db;
  protected $aValues = array();
  protected $sCollation = '';

  public function __construct(sql\Connection $db, $sCollation = 'utf8_general_ci') {

    $this->db = $db;
    $this->setCollation($sCollation);
  }

  protected function setCollation($sValue) {

    $this->sCollation = $sValue;
  }

  protected function getCollation() {

    return $this->sCollation;
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

        if ($sOp === 'search') {

          $val1 .= ' COLLATE ' . $this->getCollation();

          $sOp = 'like';
          $val2 = "%$val2%";
        }

        $val2 = $sql->escape($val2);
      }

      $this->addStatic("$val1 $sOp $val2");
    }
    else if (!is_null($sDefault) && $sDefault !== '') {

      $val2 = is_array($val2) ? '(' . $sDefault . ')' : $sDefault;

      $this->addStatic("$val1 $sOp $val2");
    }

  }

  public function addStatic($sValue, $sLogic = ' AND ') {

    $this->aValues[] = $sValue;
  }

  protected function getValues() {

    return $this->aValues;
  }

  public function __toString() {

    return $this->getValues() ? ' WHERE ' . implode(' AND ', $this->getValues()) : '';
  }
}

