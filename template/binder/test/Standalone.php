<?php

namespace sylma\template\binder\test;
use sylma\core, sylma\dom, sylma\storage\fs, sylma\modules\tester, sylma\storage\sql;

class Standalone extends tester\Parser implements dom\domable {

  //const NS = 'http://www.sylma.org/parser/js/binder/test';

  protected $iTestKey;

  public function __construct(fs\file $file, $iTest) {

    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');

    $this->resetDB();

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

  public function getScript($sPath, array $aArguments = array(), array $aContexts = array(), array $aPosts = array()) {

    return parent::getScript($sPath, $aArguments, $this->getActionContexts()->query(), $aPosts);
  }

  public function asDOM() {

    $doc = $this->getFile()->getDocument($this->getNS());

    $test = $this->loadTest($doc);

    if (!$result = $this->parseResult($test, $this->getFile(), array('contexts' => $this->getActionContexts()))) {

      $this->prepareTest($test, $this);
      $result = $this->get('result');
    }

    $bCallback = 0;

    if (!$expected = $test->getx('self:expected', array(), false)) {

      $expected = $test->getx('self:callback');
      $bCallback = 1;
    }

    $sExpected = $expected->readx();

    $parent = $this->getManager('parser')->getContext('action/current');

    $sBind = $expected->readx('@bind', array(), false);
    if (!$sBind) $sBind = 'example';

    $parent->getContext('js/load')->add("sylma.tester.main.run(function() { $sExpected; }, $sBind, $bCallback);");

    return $result;
  }
}

