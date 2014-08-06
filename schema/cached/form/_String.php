<?php

namespace sylma\schema\cached\form;
use sylma\core;

class _String extends Type {

  protected function validateFormat() {

    $val = $this->getValue();

    return is_string($val) || is_numeric($val);
  }
}

