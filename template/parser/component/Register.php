<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template\parser;

class Register extends Child implements common\arrayable, parser\component {

  public function parseRoot(dom\element $el) {


  }

  public function asArray() {

    $this->getParser()->register($this->getTemplate()->getTree());
    return array();
  }
}

