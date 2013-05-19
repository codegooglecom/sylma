<?php

namespace sylma\storage\sql\template\view;
use sylma\core, sylma\storage\sql;

class Field extends sql\template\component\Field implements sql\template\pathable {

  protected $bInserted = false;

  protected function reflectApplySelf($sMode = '') {

    if ($result = parent::reflectApplySelf($sMode)) {

      $this->addToQuery();
    }
    else {

      $result = $this->reflectRead();
    }

    return $result;
  }

  protected function addToQuery() {

    if (!$this->bInserted) {

      $query = $this->getQuery();
      $query->setColumn($this);

      $this->bInserted = true;
    }
  }

  public function reflectRead() {

    $this->addToQuery();

    return $this->reflectSelf();
  }
}
