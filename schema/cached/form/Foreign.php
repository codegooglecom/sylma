<?php

namespace sylma\schema\cached\form;
use sylma\core;

class Foreign extends _Integer {

  protected function isMultiple() {

    return $this->read('multiple', false);
  }

  public function isNull() {

    return !$this->getValue();
  }

  protected function validateValue() {

    if ($this->isMultiple()) {

      $this->isUsed(false);
      $bResult = is_array($this->getValue());
    }
    else {

      $bResult = parent::validateValue();
    }

    return $bResult;
  }
}

