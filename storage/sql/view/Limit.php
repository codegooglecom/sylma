<?php

namespace sylma\storage\sql\view;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Limit extends reflector\component\Foreigner implements reflector\component, common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);

    $this->allowForeign(true);
    $this->allowText(true);
  }

  public function asArray() {

    $tree = $this->getParser()->getTree();
    $query = $tree->getQuery();

    if ($this->getNode()->isComplex()) {

      $content = $this->parseChildren($this->getNode()->getChildren());
    }
    else {

      $content = $this->readx();
    }

    $this->log('SQL : limit');

    $query->setCount($content);

    return array();
  }
}

