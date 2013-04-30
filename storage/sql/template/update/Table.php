<?php

namespace sylma\storage\sql\template\update;
use sylma\core, sylma\storage\sql, sylma\schema\parser, sylma\parser\languages\common;

class Table extends sql\template\insert\Table {

  public function getQuery() {

    if (!$this->query) {

      $this->setQuery($this->createQuery('update'));
    }

    return $this->query;
  }
}

