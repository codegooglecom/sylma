<?php

namespace sylma\schema\parser\component;
use sylma\core, sylma\dom, sylma\schema\parser, sylma\parser\reflector;

class Basic extends reflector\component\Foreigner {

  public function parseRoot(dom\element $el) {

    $this->setNode($el, false);

    // do nothing
  }
}

