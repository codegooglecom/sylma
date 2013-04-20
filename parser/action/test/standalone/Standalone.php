<?php

namespace sylma\parser\action\test\standalone;
use sylma\modules\tester, sylma\core, sylma\dom, sylma\storage\fs, sylma\parser\action;

class Standalone extends tester\Prepare implements core\argumentable {

  const NS = 'http://www.sylma.org/parser/action/test/standalone';

  protected $sTitle = 'Standalone';

  public function __construct(parser\action\Manager $controler = null) {

    \Sylma::getControler('dom');

    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');
    $this->setNamespace(action\handler::NS, 'le', false);

    if (!$controler) $controler = $this;
    //if (!$controler) $controler = \Sylma::getControler('action');

    $this->setControler($controler);
  }

  public function getDirectory($sPath = '', $bDebug = true) {

    return parent::getDirectory($sPath, $bDebug);
  }

  public function getArgument($sPath, $bDebug = true, $mDefault = null) {

    return parent::getArgument($sPath, $bDebug, $mDefault);
  }

  public function setArgument($sPath, $mValue) {

    return parent::setArgument($sPath, $mValue);
  }

  protected function test(dom\element $test, $sContent, $controler, dom\document $doc, fs\file $file) {

    if ($node = $test->getx('self:node', array(), false)) {

      $this->setArgument('node', $node->getFirst());
    }

    return parent::test($test, $sContent, $controler, $doc, $file);
  }

  public function createArgument($mArguments, $sNamespace = '') {

    return parent::createArgument($mArguments, $sNamespace);
  }

  public function getAction($sPath, array $aArguments = array()) {

    return parent::readAction($sPath, $aArguments);
  }
}

