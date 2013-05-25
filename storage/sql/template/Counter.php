<?php

namespace sylma\storage\sql\template;
use sylma\core, sylma\parser\reflector, sylma\parser\languages\common, sylma\storage\sql;

class Counter extends reflector\component\Foreigner implements reflector\component, common\arrayable {

  protected $query;

  public function setQuery(sql\query\parser\Select $query) {

    $query->clearColumns();
    $query->clearLimit();
    $query->clearOrder();

    $query->setColumn('COUNT(*)');
    $query->isMultiple(false);
    $query->setMethod('read');

    $this->query = $query;
  }

  protected function getQuery() {

    return $this->query;
  }

  public function getVar() {

    return $this->getQuery()->getVar();
  }

  public function asArray() {

    if (!$this->getQuery()->isEmpty()) {

      $aResult[] = $this->getQuery()->getVar()->getInsert();
    }
    else {

      $aResult[] = null;
    }

    return $aResult;
  }
}

