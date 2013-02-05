<?php

namespace sylma\parser\reflector\basic;
use \sylma\core, sylma\parser\languages\common, sylma\dom, sylma\parser\reflector;

abstract class Child extends Namespaced {

  protected $parent;
  const ARGUMENTS = '';

  protected function setParent(reflector\domed $parent) {

    if ($parent === $this) {

      $this->throwException('Cannot set itself as parent');
    }

    //if ($this->getParent()) $this->throwException('Cannot set parent twice');

    $this->parent = $parent;
  }

  /**
   *
   * @return parser\reflector\domed
   */
  protected function getParent() {

    return $this->parent;
  }

  public function getParentParser() {

    $result = null;
    $parent = $this->getParent();

    if ($parent) {

      $result = $parent->getParentParser();

      if (!$result) {

        $result = $parent;
      }
    }

    return $result;
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
