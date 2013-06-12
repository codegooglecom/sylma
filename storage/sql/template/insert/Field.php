<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\storage\sql;

class Field extends sql\template\component\Field {

  const MSG_MISSING = 'The field %s is missing';

  public function reflectRegister($content = null) {

    $this->getParent()->addElementToHandler($this, $this->getDefault(), $content);
  }

  protected function reflectSelf() {

    //return null;
    $this->launchException('No self reflect');
  }
}

