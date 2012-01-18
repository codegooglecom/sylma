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
    
    if (!$aFiles = $this->getFiles()) {
      
      $aFiles = $this->getDirectory()->getFiles(array('xml'), null, null);
    }
    
    foreach ($aFiles as $file) {
      
      $doc = $file->getDocument();
      $doc->registerNamespaces($this->getNS());
      
      if (!$doc || $doc->isEmpty()) $this->throwException(txt('@file %s cannot be load'));
      
      $aTests = $this->loadDocument($doc, $file);
      
      $aResult[] = array(
        'description' => $doc->readx('self:description', $this->getNS()),
        '#test' => $aTests,
      );
    }
    
    $this->onFinish();
    
    return $aResult;
  }
  
  protected function loadDocument(dom\handler $doc, fs\file $file) {

    $aResult = array();
    
    foreach ($doc->queryx('self:test') as $test) {
      
      if (!$test->testAttribute('disabled', false)) {
        //dspf($test->readAttribute('disabled'));
        $bResult = $this->test($test, $this->getControler(), $doc, $file);
        
        $aTest = array(
          '@name' => $test->getAttribute('name'),
          'result' => booltostr($bResult),
        );

        if (!$bResult) $aTest['message'] = ''; // ? TODO suspicious..
        
        $aResult[] = $aTest;
      }
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
      
      $sCatch = $test->readAttribute('catch', null, false);
      
      if ($sCatch && $e instanceof $sCatch) {
        
        $bResult = true;
      }
      else {
        
        $e->save();
      }
    }
    
    return $bResult;
  }
  
  public function setControler(core\factory $controler, $sName = '') {
    
    if ($sName) parent::setControler($controler, $sName);
    else $this->controler = $controler;
  }
  
  public function getNamespace($sPrefix = null) {
    
    return parent::getNamespace($sPrefix);
  }
  
  public function parse() {
    
    $result = $this->createArgument(array(
      'group' => array(
        'description' => t($this->sTitle),
        '#group' => $this->load(),
      ),
    ), self::NS);
    
    return $result->asDOM();
  }
  
  protected function onFinish() {
    
    
  }
}