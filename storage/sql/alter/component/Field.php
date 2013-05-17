<?php

namespace sylma\storage\sql\alter\component;
use sylma\core, sylma\storage\sql;

class Field extends sql\schema\component\Field {

  public function asUpdate() {

    $sResult = '';

    $previous = $this->getPrevious();
    $sPosition = $previous ? " AFTER `{$previous->getName()}`" : ' FIRST';

    if ($col = $this->getParent()->getColumn($this->getName())) {

      $sName = $this->getName();
      $sResult = "CHANGE `{$sName}` " . $this->asString() . $sPosition;
    }
    else {

      $sResult = "ADD " . $this->asString() . $sPosition;
    }

    return $sResult;
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

    return $this->getType()->asString() . ($this->isID() ? ' AUTO_INCREMENT' : '');
  }

  public function asString() {

    return "`{$this->getName()}` " . $this->typeAsString(); // " COMMENT '{$this->getTitle()}'";
  }
}

