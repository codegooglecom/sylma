<?php

namespace sylma\core\argument\test;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs;

class Basic extends tester\Prepare implements core\argumentable {

  const NS = 'http://www.sylma.org/core/argument/test';
  protected $sTitle = 'Basic';

  public function __construct() {

    $this->getControler('dom');

    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');

    $this->setArguments('settings.yml');

    $controler = $this->create('controler', array($this, $this->getDirectory('samples')));
    $controler->setArguments($this->getArguments());

    $this->setControler($controler);
  }

  protected function openDocument(fs\file $file) {

    if ($result = parent::openDocument($file)) {

      if ($result->getRoot() && ($sClass = $result->getRoot()->readAttribute('class', $this->getNamespace(), false))) {

         $this->getControler()->setArgument('class-alias', $sClass);
      }
    }

    return $result;
  }

  protected function test(dom\element $test, $sExpected, $controler, dom\document $doc, fs\file $file) {

    if ($nodeResult = $test->getx('self:node', array(), false)) {

      $this->setArgument('node', $nodeResult->getFirst());
    }

    return parent::test($test, $sExpected, $controler, $doc, $file);
  }
}

