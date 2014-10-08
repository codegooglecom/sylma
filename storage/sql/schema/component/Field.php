<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\dom, sylma\storage\sql, sylma\schema;

class Field extends Element implements sql\schema\field {

  public function setParent(schema\parser\element $parent) {

    $this->setTable($parent);
    return parent::setParent($parent);
  }

  protected function setTable(Table $table) {

    $this->table = $table;
  }

  /**
   *
   * @return Table
   */

  protected function getTable() {

    return $this->table;
  }

  public function parseRoot(dom\element $el) {

    parent::parseRoot($el);
    $this->loadOptional();
  }
}

