<?php

namespace sylma\view\parser\component;
use sylma\core, sylma\dom, sylma\template, sylma\parser\languages\common;

class getSchema extends template\parser\component\Child implements template\parser\component, common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
  }

  public function asArray() {

    return array((string) $this->getParser()->getSchema()->getSourceFile());
  }
}

