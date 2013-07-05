<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template\parser;

class Register extends Child implements common\arrayable, parser\component {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);

    $this->allowText(true);
    $this->allowForeign(true);
  }

  protected function loadContent() {

    return $this->parseComponentRoot($this->getNode(), false);
  }

  public function asArray() {

    return array();
  }
}

