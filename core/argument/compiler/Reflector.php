<?php

namespace sylma\core\argument\compiler;
use sylma\core, sylma\parser, sylma\dom, sylma\parser\languages\common, sylma\storage\fs;

\Sylma::load('/parser/Reflector.php');

/**
 * Description of Reflector
 *
 * @author Rodolphe Gerber
 */
class Reflector extends parser\Reflector {

  const NS = 'http://www.sylma.org/core/argument';

  public function __construct(core\factory $manager, dom\handler $doc, fs\directory $dir) {

    $this->setDocument($doc);
    $this->setControler($manager);

    //$this->setNamespace(self::NS);
    //$this->setDirectory($dir);
  }

  protected function parseDocument(dom\document $doc) {

    if ($doc->isEmpty()) {

      $this->throwException(t('empty doc'));
    }

    $doc->registerNamespaces($this->getNS());

    $this->getWindow()->setContext(common\_window::CONTEXT_DEFAULT);
    $this->parseChildren($doc->getChildren(), true);

    return $this->getWindow();
  }

  public function build() {

    return $this->parseDocument($this->getDocument());
  }

  public function asDOM() {

    $window = $this->build();
    $result = $window->asArgument()->asDOM();

    return $result;
  }
}

?>
