<?php

namespace sylma\view\parser\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Schema extends reflector\component\Foreigner implements reflector\component, common\arrayable {

  public function parseRoot(dom\element $el) {

    $sFile = $el->read();

    return $this->getSourceFile($sFile);
  }

  public function asArray() {

    return array();
  }
}

