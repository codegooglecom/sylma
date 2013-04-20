<?php

namespace sylma\template\binder\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\template;

class Basic extends reflector\component\Foreigner {

  public function getPHPWindow() {

    return $this->getParser()->getPHPWindow();
  }
}

