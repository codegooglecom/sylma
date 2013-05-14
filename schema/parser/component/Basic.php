<?php

namespace sylma\schema\parser\component;
use sylma\core, sylma\dom, sylma\schema\parser, sylma\parser\reflector;

class Basic extends reflector\component\Foreigner {

  protected $sName = '';
  
  public function parseRoot(dom\element $el) {

    $this->setNode($el, false);

    // do nothing
  }

  public function setName($sName) {

    $this->sName = $sName;
  }

  public function getName() {

    return $this->sName;
  }

  public function asToken() {

    return $this->getName();
  }
}

