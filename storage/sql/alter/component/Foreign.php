<?php

namespace sylma\storage\sql\alter\component;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common, sylma\schema\parser;

class Foreign extends sql\schema\component\Foreign implements sql\alter\alterable {

  public function asUpdate() {

    return $this->getParent()->fieldAsUpdate($this, $this->getPrevious());
  }

  public function asCreate() {

    $ref = $this->getElementRef();

    if (!$this->getMaxOccurs(true)) {

      if ($file = $this->getElementRefFile()) {

        $this->getParent()->buildSchema($file);
      }

      $sResult = $this->asString() . ",CONSTRAINT FOREIGN KEY ({$this->getName()}) REFERENCES {$ref->getName()} (id)";
    }
    else {

      $sResult = '';
    }

    return $sResult;
  }

  public function asJunction() {

    if ($this->getMaxOccurs(true)) {

      $this->loadJunction();
    }
  }

  protected function typeAsString() {

    if (!$sDefault = $this->readx('@alter-default')) {

      $sDefault = $this->getDefault();
    }

    $sContent = $sDefault ? ' NULL' . ($this->getDefault() ? ' DEFAULT ' . $this->getDefault() : '') : ' NOT NULL';

    return "BIGINT UNSIGNED" . $sContent;
  }

  public function asString() {

    return "`{$this->getName()}` " . $this->typeAsString(); // " COMMENT '{$this->getTitle()}'";
  }
}

