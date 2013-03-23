<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\storage\sql, sylma\schema\parser, sylma\parser\languages\common;

class Table extends sql\template\component\Table {

  public function getQuery() {

    if (!$this->query) {

      $this->setQuery($this->createQuery('insert'));
    }

    return $this->query;
  }

  public function reflectApply($sPath, $sMode = '*') {

    return parent::reflectApply($sPath, $sMode);
  }
}

