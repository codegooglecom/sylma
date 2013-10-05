<?php

namespace sylma\storage\sql\view\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Distinct extends reflector\component\Foreigner implements reflector\component, common\arrayable {

  protected $var;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->allowText(true);
  }

  public function asArray() {

    $tree = $this->getParser()->getCurrentTree();
    $query = $tree->getQuery();

    $el = $tree->getElement($this->readx('@name', true));

    $this->log('SQL : distinct');

    $query->setElement($el, true);

    return array();
  }
}

