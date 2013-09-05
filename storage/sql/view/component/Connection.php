<?php

namespace sylma\storage\sql\view\component;
use sylma\core, sylma\dom, sylma\storage\sql;

class Connection extends Basic {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->getFromTree($this->getParser()->getTree());
  }

  protected function getFromTree(sql\template\component\Table $tree) {

    return $tree->loadConnection($this->readx());
  }
}

