<?php

namespace sylma\modules\tester;
use \sylma\core, \sylma\dom, \sylma\storage\fs;

abstract class Prepare extends Basic {

  protected function test(dom\element $test, $sExpected, $controler, dom\document $doc, fs\file $file) {

    $bResult = false;
    $bReady = true;

    $sPrepare = $test->readx('self:prepare', array(), false);
    $sExpected = $test->readx('self:expected', array(), false);

    try {

      if ($sPrepare) {

        if (is_null(eval('$closure = function($controler) { $manager = $controler; ' . $sPrepare . '; };'))) {

          $this->evaluate($closure, $controler);
          $this->onPrepared();
        }
        else {

          $bReady = false;
        }
      }

      if ($bReady) {

        if ($sExpected) {

          if (is_null(eval('$closure = function($controler) { $manager = $controler; ' . $sExpected . '; };'))) {

            $bResult = $this->evaluate($closure, $controler);
          }
        }
        else {

          $result = $this->getArgument('result');
          $node = $this->getArgument('node');

          $bResult = $this->compareNodes($result, $node);
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