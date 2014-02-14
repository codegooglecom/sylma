<?php

namespace sylma\template\binder\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\template\binder;

class Basic extends binder\Basic {

  protected $bBuilded = false;

  protected function getObject($bDebug = true) {

    return $this->getHandler()->getObject($bDebug);
  }
/*
  protected function extractClass(binder\Basic $obj) {

    return $obj instanceof binder\_class ? $obj : $obj->getClass();
  }
*/
  protected function isBuilt($bValue = null) {

    if (is_bool($bValue)) $this->bBuilded = $bValue;

    return $this->bBuilded;
  }
}

