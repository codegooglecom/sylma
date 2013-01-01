<?php

namespace sylma\parser\xslt\compiler;
use sylma\core, sylma\parser, sylma\dom, sylma\parser\languages\common, sylma\storage\fs;

\Sylma::load('/parser/reflector/basic/Documented.php');
\Sylma::load('/parser/reflector/documented.php');

/**
 * Description of Reflector
 *
 * @author Rodolphe Gerber
 */
class Reflector extends parser\reflector\basic\Documented implements parser\reflector\documented {

  public function __construct(core\factory $manager, dom\handler $doc, fs\directory $dir = null) {

    $this->setDocument($doc);
    $this->setControler($manager);

    //$this->setNamespace(self::NS, 'arg', false);
    //$this->setDirectory($dir);
  }

  protected function parseDocument(dom\document $doc) {

    if ($doc->isEmpty()) {

      $this->throwException('Empty document');
    }

    //$doc->registerNamespaces($this->getNS());

    $mResult = $this->parseChildren($doc->getChildren());

    $this->getWindow()->add($array);

    return $this->getWindow();
  }

  public function build() {

    return $this->parseDocument($this->getDocument());
  }

  public function asDOM() {

    $window = $this->build();
    $arg = $window->asArgument();
    //$this->show($arg, false);
    $result = $arg->asDOM();

    return $result;
  }
}

?>
