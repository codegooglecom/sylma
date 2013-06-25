<?php

namespace sylma\storage\sql\view\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Limit extends reflector\component\Foreigner implements reflector\component, common\arrayable {

  protected $bBuilded = false;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);

    $this->allowForeign(true);
    $this->allowText(true);
  }

  protected function build() {

    $tree = $this->getParser()->getCurrentTree();
    $query = $tree->getQuery();

    if ($this->getNode()->isComplex()) {

      $content = $this->parseChildren($this->getNode()->getChildren());
    }
    else {

      $content = $this->readx();
    }

    $this->log('SQL : limit');

    $query->setCount($content);
  }

  public function asArray() {

    if (!$this->bBuilded) {

      $this->build();
      $this->bBuilded = true;
    }

    return array();
  }
}

