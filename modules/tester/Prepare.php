<?php

namespace sylma\modules\tester;
use \sylma\core, \sylma\dom, \sylma\storage\fs;

require_once('Basic.php');

abstract class Prepare extends Basic {
  
  protected function test(dom\element $test, $controler, dom\document $doc, fs\file $file) {
    
    $bResult = false;
    
    if (!$prepare = $test->get('self:prepare', $this->getNS())) {
      
      $this->throwException('No document defined', '@file ' . $file);
    }
    
    if (!$expected = $test->get('self:expected', $this->getNS())) {
      
      $this->throwException('No expectation defined', '@file ' . $file);
    }
    
    $sPrepare = $prepare->read();
    $sExpected = $expected->read();
    
    try {
      
      if (eval('$closure = function($controler) { ' . $sPrepare . '; };') === null) {
        
        $mResult = $closure($controler);
        
        $this->onPrepared($mResult);
        
        if (eval('$closure = function($controler) { ' . $sExpected . '; };') === null) {
          
          $bResult = $closure($controler);
        }
      }
    }
    catch (core\exception $e) {
      
    }
    
    return $bResult;
  }
  
  protected function onPrepared($mResult) {
    
    
  }
}