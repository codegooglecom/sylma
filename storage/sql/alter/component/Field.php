<?php

namespace sylma\storage\sql\alter\component;
use sylma\core, sylma\storage\sql;

class Field extends sql\schema\component\Field implements sql\alter\alterable {

  public function asUpdate() {

    return $this->getParent()->fieldAsUpdate($this, $this->getPrevious());
  }

  public function asCreate() {

    $sResult = $this->asString();

    if ($this->isID()) {

      $sResult .= ",PRIMARY KEY (`{$this->getName()}`)";
    }

    return $sResult;
  }

  protected function isID() {

    $id = $this->getParser()->getType('id', $this->getNamespace('sql'));
    return $this->getType()->doExtends($id);
  }

  protected function typeAsString() {

    if (!$sDefault = $this->readx('@alter-default')) {

      $sDefault = $this->getDefault();
    }

    $sDefault = $this->isRequired() ? ' NOT NULL' : ' NULL' . ($this->getDefault() ? ' DEFAULT ' . $sDefault : '');

    return $this->getType()->asString() . ($this->isID() ? ' AUTO_INCREMENT' : $sDefault);
  }

  public function asString() {

    return "`{$this->getName()}` " . $this->typeAsString(); // " COMMENT '{$this->getTitle()}'";
  }
}

