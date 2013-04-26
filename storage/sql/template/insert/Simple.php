<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common;

class Simple extends sql\template\component\Simple {

  protected $var;

  protected function createObject(common\_var $handler) {

    $window = $this->getWindow();

    $sClass = $this->getFactory()->findClass('cached')->read('name');
    $instance = $window->tokenToInstance($sClass);

    return $window->addVar($window->createInstanciate($instance, array($handler)));
  }

  protected function getHandler() {

    return $this->getParser()->getReflector();
  }

  public function escape(common\callable $var) {

    return $this->getVar()->call('escape', array($var));
  }

  protected function getVar() {

    if (!$this->var) {

      $this->setVar($this->createObject($this->getHandler()));
    }

    return $this->var;
  }

  protected function setVar(common\_var $obj) {

    $this->var = $obj;
  }
}

