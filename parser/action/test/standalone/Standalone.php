<?php

namespace sylma\parser\action\test\standalone;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs, \sylma\parser;

require_once('modules/tester/Prepare.php');

class Standalone extends tester\Prepare {

  const NS = 'http://www.sylma.org/parser/action/test/standalone';

  protected $sTitle = 'Standalone';

  public function __construct(parser\action\Controler $controler = null) {

    \Sylma::getControler('dom');

    require_once('parser/action.php');

    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');
    $this->setNamespace(parser\action::NS, 'le', false);

    if (!$controler) $controler = $this;
    //if (!$controler) $controler = \Sylma::getControler('action');

    $this->setControler($controler);
  }
  public function getArgument($sPath, $mDefault = null, $bDebug = false) {

    return parent::getArgument($sPath, $mDefault, $bDebug);
  }

  public function setArgument($sPath, $mValue) {

    return parent::setArgument($sPath, $mValue);
  }

  public function compareNodes(dom\document $node1, dom\node $node2) {

    $node1 = $node1->getRoot();
    
    return $node1->asString() === $node2->asString();
  }

  protected function test(dom\element $test, $controler, dom\document $doc, fs\file $file) {

    if ($node = $test->getx('self:node', array(), false)) {

      $this->setArgument('node', $node->getFirst());
    }

    return parent::test($test, $controler, $doc, $file);
  }

  public function getAction($sPath, array $aArguments = array()) {

    return parent::getAction($sPath, $aArguments);
  }
}

