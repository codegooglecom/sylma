<?php

namespace sylma\schema\cached\form;
use sylma\core;

class _String extends Type {

  protected function validateFormat() {

    return is_string($this->getValue());
  }
}

