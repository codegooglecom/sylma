<?php

namespace sylma\storage\sql\template\update;
use sylma\core, sylma\storage\sql, sylma\schema\parser, sylma\parser\languages\common;

class Table extends sql\template\insert\Table {

  protected $sMode = 'update';

  protected function loadQuery() {

    if (!$this->useElements()) {

      $result = null;
    }
    else {

      $result = parent::loadQuery();
    }

    return $result;
  }

  protected function loadTriggers() {

    $aResult = parent::loadTriggers();
    $aResult[] = $this->getWindow()->toString('1', $this->getParser()->getView()->getResult());

    return $aResult;
  }
}

