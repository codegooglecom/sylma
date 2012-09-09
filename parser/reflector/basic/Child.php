<?php

namespace sylma\parser\reflector\basic;
use \sylma\core, sylma\parser\languages\common, sylma\dom, sylma\parser;

require_once('core/module/Filed.php');

abstract class Child extends core\module\Filed {

  public function setParent(parser\reflector\documented $parent) {

    $this->parent = $parent;
  }

  protected function getParent() {

    return $this->parent;
  }

  protected function getWindow() {

    if (!$this->getParent()) {

      $this->throwException('Cannot get window because no parent is defined');
    }

    return $this->getParent()->getWindow();
  }
}
