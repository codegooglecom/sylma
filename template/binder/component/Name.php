<?php

namespace sylma\template\binder\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Name extends Basic implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);

    $this->allowForeign(true);
    $this->allowText(true);
  }

  public function asArray() {

    $this->log('JS : name');

    $obj = $this->getObject();
    $window = $this->getPHPWindow();
    $obj->setName($window->toString($this->parseComponentRoot($this->getNode())));

    return array();
  }
}

