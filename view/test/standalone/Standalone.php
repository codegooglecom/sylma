<?php

namespace sylma\view\test\standalone;
use sylma\modules\tester, sylma\core, sylma\dom, sylma\storage\fs;

class Standalone extends tester\Prepare {

  protected $sTitle = 'Standalone';

  public function __construct(parser\action\Manager $controler = null) {

    //$this->throwException('test');
    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');

    $this->setManager($this);
    $this->setFiles(array($this->getFile('basic.xml')));
  }

  public function getDirectory($sPath = '', $bDebug = true) {

    return parent::getDirectory($sPath, $bDebug);
  }

  public function getArgument($sPath, $mDefault = null, $bDebug = false) {

    return parent::getArgument($sPath, $mDefault, $bDebug);
  }

  public function setArgument($sPath, $mValue) {

    return parent::setArgument($sPath, $mValue);
  }

  protected function test(dom\element $test, $sContent, $controler, dom\document $doc, fs\file $file) {

    if ($node = $test->getx('self:node', array(), false)) {

      $this->setArgument('node', $node->getFirst());
    }

    return parent::test($test, $sContent, $this, $doc, $file);
  }

  public function getAction($sPath, array $aArguments = array()) {

    $manager = $this->getManager('action');

    return $manager->getAction($sPath, $aArguments, $this->getDirectory());
  }
}

