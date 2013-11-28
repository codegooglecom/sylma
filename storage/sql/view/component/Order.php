<?php

namespace sylma\storage\sql\view\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Order extends reflector\component\Foreigner implements reflector\component, common\arrayable {

  protected $var;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->allowForeign(true);
    $this->allowText(true);
  }

  public function asArray() {

    $tree = $this->getParser()->getCurrentTree();
    $query = $tree->getQuery();

    if ($this->getNode()->isComplex()) {

      $content = $this->parseComponentRoot($this->getNode());
      $query->setOrderDynamic($content);
    }
    else {

      $sElement = $this->readx();

      $query->setOrderPath($sElement);
    }

    $this->log('SQL : order');

    return array();
  }
}

