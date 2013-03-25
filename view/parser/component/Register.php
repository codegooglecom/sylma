<?php

namespace sylma\view\parser\component;
use sylma\core, sylma\template;

class Register extends template\parser\component\Register {

  public function parseRoot(\sylma\dom\element $el) {

    return parent::parseRoot($el);
  }

  public function asArray() {

    $tree = $this->getTemplate()->getTree();
    $tree->reflectRegister();

    return parent::asArray();
  }
}

