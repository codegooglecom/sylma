<?php

namespace sylma\modules\tester\stepper;
use sylma\core, sylma\dom, sylma\storage\fs, sylma\modules\tester;

class Standalone extends tester\Parser implements dom\domable {

  //const NS = 'http://www.sylma.org/parser/js/binder/test';
  //const DEBUG_RUN = false;

  protected $iTestKey;

  public function __construct(core\argument $args, core\argument $post, core\argument $contexts) {

    $this->contexts = $contexts;
  }

  public function initTest(fs\file $file, $iTest) {

    $this->setDirectory($file->getParent());
    $this->setNamespace(self::NS, 'self');

    $this->setManager($this);

    //$this->resetDB();

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

  public function getScript($sPath, array $aArguments = array(), array $aContexts = array(), array $aPosts = array(), $bRun = true) {

    return parent::getScript($sPath, $aArguments, $this->contexts->query(), $aPosts, $bRun);
  }

  public function asDOM() {

    $doc = $this->getFile()->getDocument($this->getNS());

    $test = $this->loadTest($doc);
    $this->prepareTest($test, $this);

    if (!$result = $this->parseResult($test, $this->getFile(), array('contexts' => $this->contexts))) {

      $this->prepareTest($test, $this);
      $result = $this->get('result');
    }

    $bCallback = 0;

    if (!$expected = $test->getx('self:expected', array(), false)) {

      $expected = $test->getx('self:callback');
      $bCallback = 1;
    }

    $sExpected = $expected->readx();

    $sBind = $expected->readx('@bind', array(), false);
    if (!$sBind) $sBind = 'example';

    $this->contexts->get('js/load')->add("$(window).addEvent('load', function() { sylma.tester.main.run(function() { $sExpected; }, $sBind, $bCallback); });");

    return $result;
  }
}

