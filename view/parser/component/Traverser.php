<?php

namespace sylma\view\parser\component;
use sylma\core, sylma\dom;

class Traverser extends Container {

  protected function loadElementUnknown(dom\element $el) {

    return $this->parseChildren($el->getChildren());
  }

  public function asArgument() {

    return $this->asArray();
  }
}

