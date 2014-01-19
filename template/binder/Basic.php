<?php

namespace sylma\template\binder;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Basic extends reflector\component\Foreigner {

  /**
   * @return sylma\template\binder\Handler
   */
  protected function getHandler() {

    return $this->getParser();
  }

  public function getPHPWindow() {

    return $this->getParser()->getPHPWindow();
  }
}

