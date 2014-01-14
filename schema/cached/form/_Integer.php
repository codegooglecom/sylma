<?php

namespace sylma\schema\cached\form;
use sylma\core;

class _Integer extends Type {

  protected function validateFormat() {

    return is_numeric($this->getValue());
  }

  public function escape() {

    $val = $this->getValue();

    if ($val) {

      $sResult = $val;
    }
    else {

      $sResult = $this->escapeEmpty();
    }

    return $sResult;
  }
}

