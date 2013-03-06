<?php

namespace sylma\view\parser\component;
use sylma\core, sylma\dom, sylma\parser\reflector;

class Schema extends reflector\component\Foreigner implements reflector\component {

  public function parseRoot(dom\element $el) {

    $sFile = $el->read();

    return $this->getSourceFile($sFile);
  }
}

