<?php

namespace sylma\view\parser\crud;
use sylma\core, sylma\dom, sylma\parser\reflector;

class _Global extends reflector\component\Foreigner implements dom\domable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
  }

  public function asDOM() {

    $dom = $this->getManager('dom');
    $collection = $dom->create('collection', array($this->getNode()->getChildren()));
    $collection->add($this->queryx('@*'));

    return $collection;
  }
}

