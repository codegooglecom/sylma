<?php

namespace sylma\modules\tester;
use sylma\core, sylma\dom, sylma\storage\fs;

class Stepper extends Basic implements core\argumentable {

  const TEST_ALIAS = 'test';

  public function getFiles() {

    return parent::getFiles();
  }

  public function getTests(fs\file $file) {

    $aResult = array();

    if ($doc = $this->openDocument($file)) {

      $aResult = $this->getTestsList($doc);
    }

    return array(static::TEST_ALIAS => $aResult);
  }

  protected function openDocument(fs\file $file) {

    $result = null;

    $doc = $file->asDocument();
    $doc->registerNamespaces($this->getNS());

    if (!$doc->isEmpty()) {

      if ($doc->getRoot()->getNamespace() === static::NS) {

        $result = $doc;
      }
    }

    return $result;
  }

  protected function show() {


  }

  protected function getTestsList(dom\handler $doc) {

    $this->show();

    $aResult = array();
    $iDisabled = 0;

    $tests = $doc->queryx('self:test');

    foreach ($tests as $iKey => $test) {

      if (!$test->testAttribute('disabled', false)) {

        $aResult[] = array(
          'id' => $iKey + 1,
          'name' => $test->readx('@name'),
        );
      }
      else {

        $iDisabled++;
      }
    }

    //$aResult['disabled'] = $iDisabled;

    return $aResult;
  }

  public function testModule(fs\file $file, $sID) {

    require_once('core/functions/Global.php');

    $bResult = false;

    if ($doc = $this->openDocument($file)) {

      if ($test = $doc->getx("self:test[position() = '$sID']")) {

        $bResult = $this->test($test, $test->read(), $this->getManager(), $doc, $file);
      }
    }

    $this->onFinish();

    return $bResult;
  }

}

