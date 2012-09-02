<?php

namespace sylma\core\argument\compiler;
use sylma\core, sylma\parser, sylma\parser\languages\common;

\Sylma::load('/parser/compiler/Basic.php');

class Main extends parser\compiler\Basic {

  public function __construct(core\factory $manager, core\argument $arguments) {

    $this->setDirectory(__FILE__);
    $this->setControler($manager);

    $this->loadDefaultArguments();
  }

  public function build(fs\file $file, fs\directory $dir) {



  }

  public function buildInto(common\_window $window) {


  }

  protected function reflectFile(fs\file $file, fs\directory $base) {

    $doc = $file->getDocument(array(), \Sylma::MODE_EXECUTE);

    try {

      $action = $this->getControler()->create('compiler/dom', array($this->getControler(), $doc, $base));
      $result = $action->asDOM();
    }
    catch (core\exception $e) {

      $e->addPath($file->asToken());
      throw $e;
    }

    return $result;
  }

}
