<?php

namespace sylma\storage\xml\test\samples;
use sylma\core;

class Cached01 extends core\module\Argumented {

  public function getPaths() {

    return implode(',', array('/abc', '/def'));
  }
}

