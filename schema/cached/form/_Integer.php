<?php

namespace sylma\schema\cached\form;
use sylma\core;

class _Integer extends Type {

  protected function validateFormat() {

    return is_numeric($this->getValue());
  }
}

