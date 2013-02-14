<?php

namespace sylma\core\argument\parser\compiler;
use sylma\core, sylma\parser\reflector, sylma\dom, sylma\parser\languages\common, sylma\storage\fs;

class Documented extends reflector\handler\Documented implements reflector\documented {

  protected function parseDocument(dom\document $doc) {

    $reflector = $this->getReflector();
    //$reflector->loadDefaultNamespace($doc->getRoot());

    $mArguments = $reflector->parseRoot($doc->getRoot());

    if (!is_array($mArguments)) {

      $window = $this->getWindow();
      $mResult = $window->callClosure($mArguments, $window->tokenToInstance('php-array'));
    }
    else {

      $mResult = $mArguments;
    }

    return $mResult;
  }
}
