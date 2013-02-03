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
  protected function getParent($bRoot = false) {

    if ($bRoot && $this->parent) {

      $result = $this->parent->getParent(true);
    }
    else if ($this->parent) {

      $result = $this->parent;
    }
    else {

      $result = $this;
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

  public function getParser($sNamespace) {

    $result = null;

    if ($this->useNamespace($sNamespace)) { // TODO : not optimal, getParser only called on foreign

      $result = $this;
    }
    else if ($this->getParent()) {

      $result = $this->getParent()->getParser($sNamespace);
    }

    return $result;
  }

}
