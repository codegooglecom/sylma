<?php

namespace sylma\core\argument\parser\compiler;
use sylma\core, sylma\parser\reflector, sylma\dom, sylma\parser\languages\common, sylma\storage\fs;

class Documented extends reflector\handler\Documented implements reflector\documented {

  //const PHP_TEMPLATE = 'basic.xsl';

  public function __construct($manager, fs\file $file, fs\directory $dir, core\argument $args = null) {

    parent::__construct($manager, $file, $dir, include('builder.xml.php'));
  }

  protected function parseReflector(reflector\domed $reflector, dom\document $doc) {

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
