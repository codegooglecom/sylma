<?php

namespace sylma\storage\sql\alter\component;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common, sylma\schema\parser;

class Foreign extends sql\schema\component\Foreign implements sql\alter\alterable {

  public function asUpdate() {

    return $this->getParent()->fieldAsUpdate($this, $this->getPrevious());
  }

  public function asCreate() {

    $ref = $this->getElementRef();
    return $this->asString() . ",CONSTRAINT FOREIGN KEY ({$this->getName()}) REFERENCES {$ref->getName()} (id)";
  }

  protected function typeAsString() {

    $sDefault = $this->isRequired() ? ' NOT NULL' : ' NULL' . ($this->getDefault() ? ' DEFAULT ' . $this->getDefault() : '');

    return "BIGINT UNSIGNED" . $sDefault;
  }

  public function asString() {

    return "`{$this->getName()}` " . $this->typeAsString(); // " COMMENT '{$this->getTitle()}'";
  }
}

