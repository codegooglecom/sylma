<?php

namespace sylma\storage\sql\view;
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
    }
    else {

      $content = ($this->readx('@dir') == 'desc' ? '!' : '') . $this->readx();
    }

    $result = $this->createObject('cached', array($content));

    $this->log('SQL : order');

    $query->setOrder($result);

    return array();
  }
}

