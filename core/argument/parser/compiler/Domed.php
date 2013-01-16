<?php

namespace sylma\core\argument\parser\compiler;
use sylma\core, sylma\parser, sylma\dom, sylma\parser\languages\common, sylma\storage\fs;

class Domed extends Reflector implements parser\reflector\documented {

  protected static $sArgumentClass = '\sylma\parser\Argument';
  protected static $sArgumentFile = 'parser/Argument.php';

  const NS = 'http://www.sylma.org/core/argument';

  public function __construct(core\factory $manager, dom\handler $doc, fs\directory $dir) {

    $this->setDocument($doc);
    $this->setControler($manager);

    $this->loadDefaultNamespace();
    $this->setNamespace(self::NS, 'arg', false);
    $this->setDirectory($dir);
  }

  protected function loadDefaultNamespace() {

    $sNamespace = $this->getDocument()->getRoot()->lookupNamespace();
    $this->setNamespace($sNamespace);
  }

  protected function parseDocument(dom\document $doc) {

    $mArguments = $this->parseElementComplex($doc->getRoot());

    if (!is_array($mArguments)) {

      $window = $this->getWindow();
      $mResult = $window->callClosure($mArguments, $window->tokenToInstance('php-array'));
    }
    else {

      $mResult = $mArguments;
    }

    return $mResult;
  }

  public function asDOM() {

    $this->build();

    $arg = $this->getWindow()->asArgument();
    //echo $this->show($arg, false);

    return $arg->asDOM();
  }
}
