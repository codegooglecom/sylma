<?php

namespace sylma\modules\tester;
use \sylma\core, \sylma\dom, \sylma\storage\fs;

require_once('modules/tester/test.php');
require_once('core/module/Domed.php');

abstract class Basic extends core\module\Domed implements test {
  
  const NS = 'http://www.sylma.org/modules/tester';
  protected $sTitle;
  protected $aFiles = array();
  
  protected function getFiles() {
    
    return $this->aFiles;
  }
  
  protected function setFiles(array $aFiles) {
    
    $this->aFiles = $aFiles;
  }
  
  public function load() {
    
    $aResult = array();
    $controler = $this->getControler();
    
    if (!$aFiles = $this->getFiles()) {
      
      $aFiles = $this->getDirectory()->getFiles(array('xml'), null, null);
    }
    
    foreach ($aFiles as $file) {
      
      $aTests = array();
      $doc = $file->getDocument();
      
      if (!$doc || $doc->isEmpty()) $this->throwException(txt('@file %s cannot be load'));
      
      foreach ($doc->query('self:test', $this->getNS()) as $test) {
        
        $bResult = $this->test($test, $controler, $doc, $file);
        
        $aTest = array(
          '@name' => $test->getAttribute('name'),
          'result' => booltostr($bResult),
        );
        
        if (!$bResult) $aTest['message'] = ''; // ? TODO suspicious..
        
        $aTests[] = $aTest;
      }
      
      $aResult[] = array(
        'description' => $doc->read('self:description', $this->getNS()),
        '#test' => $aTests,
      );
    }
    
    return $aResult;
  }
  
  protected function evaluate($closure, $controler) {
    
    return $closure($controler);
  }
  
  protected function test(dom\element $test, $controler, dom\document $doc, fs\file $file) {
    
    $bResult = false;
    
    try {
      
      if (eval('$closure = function($controler) { ' . $test->read() . '; };') === null) {
        
        $bResult = $this->evaluate($closure, $controler);
      }
    }
    catch (core\exception $e) {
      
    }
    
    return $bResult;
  }
  
  public function parse() {
    
    $result = \Arguments::buildDocument(array(
      'group' => array(
        'description' => t($this->sTitle),
        '#group' => $this->load(),
      ),
    ), self::NS);
    
    return $result;
  }
}