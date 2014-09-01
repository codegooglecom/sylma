<?php

namespace sylma\template\binder\test;
use sylma\core, sylma\dom, sylma\storage\fs, sylma\modules\tester;

class Module extends tester\Parser {

  const TEST_ALIAS = 'testjs';

  public function __construct() {

    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');
  }
/*
  public function asDOM() {

    $aTests = array();
    $files = $this->getDirectory()->getFiles(array('xml'));

    foreach ($files as $file) {

      $doc = $file->getDocument($this->getNS());

      foreach ($doc->queryx('self:test') as $test) {

        $iKey = $test->readx('count(preceding-sibling::*)');

        $aTests[] = array(
          'name' => $test->readx('@name'),
          'file' => (string) $file->getName(),
          'key' => $iKey,
        );
      }
    }

    $sParent = 'sylma.binder';
    $sTests = $this->createArgument($aTests)->asJSON();

    $action = $this->getControler('parser')->getContext('action/current');
    $action->getContext('js')->add("$sParent.tests = $sTests;");
    $action->getContext('js/load')->add("$sParent.run()");
  }
*/
}

