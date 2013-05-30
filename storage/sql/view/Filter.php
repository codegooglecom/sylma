<?php

namespace sylma\storage\sql\view;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Filter extends reflector\component\Foreigner implements reflector\component, common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->allowForeign(true);

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

    $sName = $this->readx('@name', true);
    $this->log("SQL : filter [$sName]");

    if ($this->readx('@optional')) {

      $query->setOptionalWhere($tree->getElement($sName, $tree->getNamespace()), '=', $this->getWindow()->toString($content));
    }
    else {

      $query->setWhere($tree->getElement($sName, $tree->getNamespace()), '=', $content);
    }
  }

  public function asArray() {

    return array($this->build());
  }

}

