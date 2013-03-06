<?php

namespace sylma\view\parser;
use sylma\core, sylma\dom, sylma\parser\reflector;

class Builder extends reflector\handler\Documented {

  protected function _parseReflector(reflector\domed $reflector, dom\document $doc) {

    return parent::parseReflector($reflector, $doc)->asArgument();
  }
}

