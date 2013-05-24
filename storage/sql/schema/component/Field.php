<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\dom, sylma\schema;

class Field extends schema\xsd\component\Element {

  public function parseRoot(dom\element $el) {

    parent::parseRoot($el);
    $this->loadOptional();
  }

  protected function loadOptional() {

    $this->bOptional = $this->readx('@optional');
  }
}

