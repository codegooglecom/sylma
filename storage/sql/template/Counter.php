<?php

namespace sylma\storage\sql\template;
use sylma\core, sylma\parser\reflector, sylma\parser\languages\common, sylma\storage\sql;

class Counter extends reflector\component\Foreigner implements reflector\component, common\arrayable {

  protected $bUsed = false;
  protected $query;

  public function setQuery(sql\query\parser\Select $query) {

    $query->isMultiple(false);
    $query->setMethod('read');

    $this->query = $query;
  }

  protected function getQuery() {

    return $this->query;
  }

  public function getVar() {

    $this->isUsed(true);

    return $this->getQuery()->getVar();
  }

  protected function isUsed($bVal = NULL) {

    if (is_bool($bVal)) $this->bUsed = $bVal;

    return $this->bUsed;
  }

  public function asArray() {

    if ($this->isUsed()) {

      $query = $this->getQuery();

      $query->clearColumns();
      $query->clearLimit();
      $query->clearOrder();
      //$query->clearJoins();

      $query->setColumn('COUNT(*)');

      $aResult[] = $this->getQuery()->getVar()->getInsert();
    }
    else {

      $aResult[] = null;
    }

    return $aResult;
  }
}

