<?php

namespace sylma\view\parser\crud;
use sylma\core, sylma\dom, sylma\parser\reflector;

class Routed extends reflector\component\Foreigner {

  public function getName() {

    $root = $this->getParser()->getRoot();

    return $this->getAlias() ? $this->getAlias() : $root::MODE_DEFAULT;
  }

  public function getAlias() {

    return $this->readx('@name');
  }
}

