<?php

namespace sylma\parser\reflector\component;
use sylma\core;

/**
 * Tricky class for a naming separation between parser and simple component
 * No other usage for the moment, may be replaced by traits one day
 */
class Simple extends Foreigner {

  public function parseRoot(dom\element $el) {

    $this->throwException('Simple component must not parse (root) element');
  }
}

