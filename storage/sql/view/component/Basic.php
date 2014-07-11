<?php

namespace sylma\storage\sql\view\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Basic extends reflector\component\Foreigner implements reflector\component {

  /**
   * @return \sylma\storage\sql\view\Resource
   */
  protected function getHandler() {

    return parent::getParser();
  }
}

