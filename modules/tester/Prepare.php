<?php

namespace sylma\modules\tester;
use \sylma\core, \sylma\dom, \sylma\storage\fs;

require_once('Basic.php');

abstract class Prepare extends Basic {

  protected function test(dom\element $test, $sExpected, $controler, dom\document $doc, fs\file $file) {

    $bResult = false;

    $sPrepare = $test->readx('self:prepare');
    $sExpected = $test->readx('self:expected');

    try {

      if (eval('$closure = function($controler) { ' . $sPrepare . '; };') === null) {

        $mResult = $this->evaluate($closure, $controler);

        $this->onPrepared($mResult);

        if (eval('$closure = function($controler) { ' . $sExpected . '; };') === null) {

          $bResult = $this->evaluate($closure, $controler);
        }
      }
    }
    catch (core\exception $e) {

      $bResult = $this->catchException($test, $e, $file);
    }

    return $bResult;
  }

  protected function onPrepared($mResult) {


  }
}