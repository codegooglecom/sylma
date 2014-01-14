<?php

namespace sylma\storage\sql\alter\component;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common, sylma\schema\parser;

class Reference extends sql\schema\component\Reference implements sql\alter\alterable {

  protected $bBuilded = false;

  public function asUpdate() {

    return '';
  }

  protected function loadElementRef() {

    if (!$this->bBuilded && !$this->getMaxOccurs(true)) {

      $file = $this->getElementRefFile();
      $this->getParent()->buildSchema($file);

      $this->bBuilded = true;
    }

    return parent::loadElementRef();
  }

  public function asCreate() {

    return '';
  }

  public function asString() {

    $this->getElementRef();

    return '';
  }
}

