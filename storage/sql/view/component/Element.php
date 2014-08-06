<?php

namespace sylma\storage\sql\view\component;
use sylma\core, sylma\dom, sylma\parser\languages\common;

class Element extends Basic implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
  }

  protected function build() {

    $tree = $this->getHandler()->getCurrentTree();
    $sName = $this->readx('@name', false);

    return $sName ? $tree->getElement($sName) : $tree;
  }

  public function asArray() {

    return array($this->build());
  }

}

