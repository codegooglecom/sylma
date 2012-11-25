<?php

namespace sylma\core\argument\parser\compiler;
use sylma\core, sylma\parser, sylma\dom, sylma\parser\languages\common, sylma\storage\fs;

/**
 * Description of Reflector
 *
 * @author Rodolphe Gerber
 */
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

    if ($doc->isEmpty()) {

      $this->throwException('Empty document');
    }

    $doc->registerNamespaces($this->getNS());

    //$array = $this->getWindow()->create('array', array($this->getWindow()));

    $mResult = $this->parseChildren($doc->getChildren());

    //if (!is_array($mResult)) $mResult = array($mResult);
    //$array->setContent($mResult);

    $this->getWindow()->add($this->getWindow()->argToInstance($mResult));

    return $this->getWindow();
  }

  public function build() {

    return $this->parseDocument($this->getDocument());
  }

  public function asDOM() {

    $window = $this->build();
    $arg = $window->asArgument();
    //echo $this->show($arg, false);
    $result = $arg->asDOM();

    return $result;
  }
}

?>
