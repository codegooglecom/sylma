<?php

namespace sylma\parser\js\binder\test;
use sylma\core, sylma\dom, sylma\storage\fs;

class Standalone extends core\module\Domed implements dom\domable {

  const NS = 'http://www.sylma.org/parser/js/binder/test';

  protected $file;
  protected $iTestKey;

  public function __construct(fs\file $file, $iTest) {

    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');

    $this->setFile($file);
    $this->setTestKey($iTest);
  }

  public function getTestKey() {

    return $this->iTestKey;
  }

  public function setTestKey($iTestKey) {

    $this->iTestKey = $iTestKey;
  }

  protected function getFile($sPath = '', $bDebug = true) {

    if (!$sPath) $result = $this->file;
    else $result = parent::getFile($sPath, $bDebug);

    return $result;
  }

  protected function setFile(fs\file $file) {

    $this->file = $file;
  }

  protected function loadTest(dom\handler $doc) {

    $iTest = $this->getTestKey();
    $result = $doc->getx("self:test[position() = $iTest]");

    return $result;
  }

  protected function loadAction(dom\element $test) {

    $cache = $this->getControler('fs/cache');
    $target = $cache->getDirectory()->addDirectory((string) $this->getDirectory());

    require_once('core/functions/Path.php');
    $sName = core\functions\path\urlize($this->getFile()->getName() . '-' . $test->readAttribute('name'));

    $el = $test->getFirst();
    $result = $this->getControler('action')->buildAction($this->createDocument($el), array(), $target, $this->getDirectory(), $sName);

    return $result;
  }

  public function asDOM() {

    $doc = $this->getFile()->getDocument($this->getNS());

    $test = $this->loadTest($doc);
    $action = $this->loadAction($test);

    $expected = $test->getx('self:expected');
    $sExpected = $expected->readx();
    $parent = $this->getControler('parser')->getContext('action/current');

    $sBind = $expected->readx('@bind', array(), false);
    if (!$sBind) $sBind = 'example';

    $parent->getContext('js/load')->add("sylma.tester.run(function() { $sExpected; }, $sBind);");

    return $action;
  }
}

