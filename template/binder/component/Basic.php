<?php

namespace sylma\template\binder\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\template\binder;

class Basic extends binder\Basic {

  protected $bBuilded = false;

  protected function getObject($bDebug = true) {

    return $this->getParser()->getObject($bDebug);
  }

  protected function extractClass(binder\_Object $obj) {

    return $obj->getClass();
  }


  protected function isBuilt($bValue = null) {

    if (is_bool($bValue)) $this->bBuilded = $bValue;

    return $this->bBuilded;
  }
}

