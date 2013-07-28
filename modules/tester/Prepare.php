<?php

namespace sylma\modules\tester;
use \sylma\core, \sylma\dom, \sylma\storage\fs;

abstract class Prepare extends Basic {

  protected function prepareTest(dom\element $test, $controler) {

    $bResult = true;

    if ($sPrepare = $test->readx('self:prepare', array(), false)) {

      if (is_null(eval('$closure = function($controler) { $manager = $controler; ' . $sPrepare . '; };'))) {

        $this->evaluate($closure, $controler);
        $this->onPrepared();
      }
      else {

        $bResult = false;
      }
    }

    return $bResult;
  }

  protected function test(dom\element $test, $sExpected, $controler, dom\document $doc, fs\file $file) {

    $bResult = false;
    $bReady = true;

    $this->resetCount();

    $sExpected = $test->readx('self:expected', array(), false);

    try {

      $this->prepareTest($test, $controler);

      if ($bReady) {

        if ($sExpected) {

          if (is_null(eval('$closure = function($controler) { $manager = $controler; ' . $sExpected . '; };'))) {

            $bResult = $this->evaluate($closure, $controler);
          }
        }
        else {

          $result = $this->getArgument('result');

          if ($node = $this->getArgument('node', false)) {

            $bResult = $this->compareNodes($result, $node);
          }
          else {

            $bResult = true;
          }
        }
      }
    }
    catch (core\exception $e) {

      $bResult = $this->catchException($test, $e, $file);
    }

    return $bResult;
  }

  protected function onPrepared() {


  }
}