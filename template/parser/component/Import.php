<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\template\parser, sylma\parser\languages\common;

class Import extends Child implements common\arrayable, parser\component {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->build();
  }

  public function build() {

    $file = $this->getSourceFile($this->readx());

    $this->getParser()->importFile($file);
  }

  public function asArray() {

    return array();
  }
}

