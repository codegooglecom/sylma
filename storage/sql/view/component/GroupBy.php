<?php

namespace sylma\storage\sql\view\component;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\storage\sql;

class GroupBy extends Basic implements common\arrayable {

  protected $var;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
  }

  protected function checkSelect(sql\query\parser\Select $query) {

    return $query;
  }

  public function asArray() {

    $tree = $this->getParser()->getCurrentTree();
    $query = $this->checkSelect($tree->getQuery());

    $el = $tree->getElement($this->readx('@element', true));

    $this->log('SQL : distinct');

    $query->setGroup($el);

    return array();
  }
}

