<?php

namespace sylma\storage\sql\view;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Order extends reflector\component\Foreigner implements reflector\component {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->allowForeign(true);
    $this->allowText(true);
    
    $this->build();
  }

  protected function build() {

    $tree = $this->getParser()->getTree();
    $query = $tree->getQuery();

    if ($this->getNode()->isComplex()) {

      $content = $this->parseChildren($this->getNode()->getChildren());
    }
    else {

      $content = "'{$this->readx()}'";
    }

    $query->setOrder($content);
  }
}

