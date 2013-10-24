<?php

namespace sylma\core\argument\parser\compiler;
use sylma\core, sylma\parser\reflector, sylma\dom, sylma\storage\fs;

class Documented extends reflector\builder\Documented implements reflector\documented {

  //const PHP_TEMPLATE = 'basic.xsl';

  public function __construct($manager, fs\file $file, fs\directory $dir, core\argument $args = null, dom\document $doc = null) {

    parent::__construct($manager, $file, $dir, include('builder.xml.php'), $doc);
  }

  public function buildStatic() {

    return $this->reflectMain($this->getDocument());
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
