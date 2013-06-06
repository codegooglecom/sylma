<?php

namespace sylma\template\binder\test;
use sylma\core, sylma\dom, sylma\storage\fs, sylma\modules\tester, sylma\storage\sql;

class Standalone extends tester\Parser implements dom\domable {

  //const NS = 'http://www.sylma.org/parser/js/binder/test';

  protected $iTestKey;

  public function __construct(fs\file $file, $iTest) {

    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');

    $arg = $this->createArgument('/#sylma/view/test/database.xml');
    \Sylma::setControler(self::DB_MANAGER, new sql\Manager($arg));

    $this->setFile($file);
    $this->setTestKey($iTest);
    $this->setSettings(array());

    $this->exportDirectory = $this->loadCacheDirectory($this->getDirectory());
  }

  public function getTestKey() {

    return $this->iTestKey;
  }

  public function setTestKey($iTestKey) {

    $this->iTestKey = $iTestKey;
  }

  protected function loadTest(dom\handler $doc) {

    $iTest = $this->getTestKey();
    $result = $doc->getx("self:test[position() = $iTest]");

    return $result;
  }

  public function asDOM() {

    $doc = $this->getFile()->getDocument($this->getNS());

    $test = $this->loadTest($doc);

    $contexts = $this->getManager('parser')->getContext('action/current')->getContexts();
    $action = $this->parseResult($test, $this->getFile(), array('contexts' => $contexts));

    $expected = $test->getx('self:expected');
    $sExpected = $expected->readx();
    $parent = $this->getManager('parser')->getContext('action/current');

    $sBind = $expected->readx('@bind', array(), false);
    if (!$sBind) $sBind = 'example';

    $parent->getContext('js/load')->add("sylma.tester.run(function(callback) { $sExpected; }, $sBind);");

    return $action;
  }
}

