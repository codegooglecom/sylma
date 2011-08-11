<?php

namespace sylma\storage\fs\test;
use \sylma\modules\tester, \sylma\storage\fs;

require_once('modules/tester/Basic.php');

class Basic extends tester\Basic {
  
  const NS = 'http://www.sylma.org/storage/fs/test';
  protected $sTitle = 'File system';
  
  private $controler;
  
  public function __construct(fs\Controler $controler) {
    
    $this->setDirectory(__file__);
    $this->setNamespaces(array('self' => self::NS));
    $this->controler = $controler;
    
    parent::__construct();
  }
  
  protected function getControler() {
    
    return $this->controler;
  }
  
  public function load() {
    
    $aResult = array();
    $controler = $this->getControler();
    
    foreach ($this->getDirectory()->getFiles(array('xml'), null, null) as $file) {
      
      $aTests = array();
      
      $doc = $file->getDocument();
      $sDirectory = (string) $file->getParent();
      
      if (!$doc || $doc->isEmpty()) $this->throwException(txt('@file %s cannot be load'));
      
      foreach ($doc->query('self:test', $this->getNS()) as $test) {
        
        $bResult = false;
        
        try {
          
          if (eval('$closure = function($sDirectory, $controler) { ' . $test->read() . '; };') === null) {
            
            $bResult = $closure($sDirectory, $controler);
          }
        }
        catch (\SylmaExceptionInterface $e) {
          
        }
        
        $aTest = array(
          '@name' => $test->getAttribute('name'),
          'result' => booltostr($bResult),
        );
        
        if (!$bResult) {
          
          $aTest['message'] = '';
        }
        
        $aTests[] = $aTest;
      }
      
      $aResult[] = array(
        'description' => $doc->read('self:description', $this->getNS()),
        '#test' => $aTests,
      );
    }
    
    return $aResult;
  }
}

