<?php

namespace sylma\storage\sql\template\update;
use sylma\core, sylma\storage\sql, sylma\schema\parser, sylma\parser\languages\common;

class Table extends sql\template\insert\Table {

  protected $sMode = 'update';

  public function asArgument() {

    $result = parent::asArgument();
    $result->addContent($this->getWindow()->toString('1', $this->getParser()->getView()->getResult()));

    return $result;
  }
}

