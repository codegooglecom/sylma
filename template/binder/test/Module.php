<?php

namespace sylma\template\binder\test;
use sylma\core, sylma\dom, sylma\storage\fs;

class Module extends core\module\Domed implements dom\domable {

  const NS = 'http://www.sylma.org/modules/tester/parser';

  protected $iTestKey;

  public function __construct() {

    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');

    $this->setFile($this->getFile('basic.xml'));
  }

  public function asDOM() {

    $doc = $this->getFile()->getDocument($this->getNS());
    $aTests = array();

    foreach ($doc->queryx('self:test') as $test) {

      $iKey = $test->readx('count(preceding-sibling::*)');

      $aTests[] = array(
        'name' => $test->readx('@name'),
        'file' => (string) $this->getFile()->getName(),
        'key' => $iKey,
      );
    }

    $sParent = 'sylma.binder';
    $sTests = $this->createArgument($aTests)->asJSON();

    $action = $this->getControler('parser')->getContext('action/current');
    $action->getContext('js')->add("$sParent.tests = $sTests;");
    $action->getContext('js-load')->add("$sParent.run()");
  }
}

