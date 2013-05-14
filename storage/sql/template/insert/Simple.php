<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common;

class Simple extends sql\template\component\Simple {

  protected $var;

  protected function getHandler() {

    return $this->getParser()->getReflector();
  }

  public function escape(common\callable $var) {

    return $this->getVar()->call('escape', array($var));
  }

  protected function getVar() {

    if (!$this->var) {

      $this->setVar($this->createObject('cached', array($this->getHandler())));
    }

    return $this->var;
  }

  protected function setVar(common\_var $obj) {

    $this->var = $obj;
  }
}

