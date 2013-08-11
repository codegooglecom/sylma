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

    $sContent = $sDefault ? ' NULL' . ($this->getDefault() ? ' DEFAULT ' . $sDefault : '') : ' NOT NULL';

    $type = $this->getType();

    if ($type->doExtends($this->getParser()->getType('datetime', $this->getNamespace('sql')))) {

      if ($sDefault) {

        $sType = 'DATETIME';
      }
      else {

        $sType = 'TIMESTAMP';
      }
    }
    else {

      $sType = $type->asString();
    }


    return $sType . ($this->isID() ? ' AUTO_INCREMENT' : $sContent);
  }

  public function asString() {

    return "`{$this->getName()}` " . $this->typeAsString(); // " COMMENT '{$this->getTitle()}'";
  }
}

