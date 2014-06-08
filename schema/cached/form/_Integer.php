<?php

namespace sylma\schema\cached\form;
use sylma\core;

class _Integer extends Type {

  protected function validateFormat() {

    return is_numeric($this->getValue());
  }

  protected function getDefault() {

    $sResult = parent::getDefault();

    if ($sResult === '') {

      $this->launchException('Empty string not allowed as integer');
    }

    return $sResult;
  }

  public function escapeValue($val) {

    return $val;
  }
}

