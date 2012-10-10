<?php

namespace sylma\parser\js\binder\test;
use sylma\core, sylma\dom, sylma\storage\fs;

class Module extends core\module\Domed implements dom\domable {

  const NS = 'http://www.sylma.org/parser/js/binder/test';

  protected $file;
  protected $iTestKey;

  public function __construct() {

    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');

    $this->setFile($this->getFile('basic.xml'));
  }

  protected function getFile($sPath = '', $bDebug = true) {

    if (!$sPath) $result = $this->file;
    else $result = parent::getFile($sPath, $bDebug);

    return $result;
  }

  protected function setFile(fs\file $file) {

    $this->file = $file;
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
    $action->getContext('js/load')->add("$sParent.run()");
  }
}

