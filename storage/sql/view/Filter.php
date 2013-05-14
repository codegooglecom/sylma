<?php

namespace sylma\storage\sql\view;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Filter extends reflector\component\Foreigner implements reflector\component, common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->allowForeign(true);
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

    $query->setWhere($tree->getElement($this->readx('@name', true), $tree->getNamespace()), '=', $content);
    //$query->isMultiple(!$this->readx('@single'));
  }

  public function asArray() {

    return array();
  }

}

