<?php

namespace sylma\storage\sql\alter\component;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common, sylma\schema\parser;

class Foreign extends sql\schema\component\Foreign {

  public function asUpdate() {

    return $this->getParent()->fieldAsUpdate($this, $this->getPrevious());
  }

  public function asCreate() {

    $ref = $this->getElementRef();
    return $this->asString() . ",CONSTRAINT FOREIGN KEY ({$this->getName()}) REFERENCES {$ref->getName()} (id)";
  }

  protected function typeAsString() {

    return "BIGINT UNSIGNED";
  }

  public function asString() {

    return "`{$this->getName()}` " . $this->typeAsString(); // " COMMENT '{$this->getTitle()}'";
  }
}

