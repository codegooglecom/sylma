<?php

namespace sylma\parser\reflector\basic;
use \sylma\core, sylma\parser\languages\common, sylma\dom, sylma\parser;

abstract class Child extends core\module\Filed {

  protected $parent;

  public function setParent(parser\reflector\documented $parent) {

    $this->parent = $parent;
  }

  /**
   *
   * @return parser\reflector\documented
   */
  protected function getParent() {

    return $this->parent;
  }

  /**
   *
   * @return common\_window
   */
  protected function getWindow() {

    if (!$this->getParent()) {

      $this->throwException('Cannot get window because no parent is defined');
    }

    return $this->getParent()->getWindow();
  }
}
