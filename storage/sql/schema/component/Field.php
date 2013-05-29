<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\dom, sylma\storage\sql\schema;

class Field extends Element implements schema\field {

  public function parseRoot(dom\element $el) {

    parent::parseRoot($el);
    $this->loadOptional();
  }
}

