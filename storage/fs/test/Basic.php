<?php

namespace sylma\storage\fs\test;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs;

require_once('modules/tester/Basic.php');

class Basic extends tester\Basic {
  
  const NS = 'http://www.sylma.org/storage/fs/test';
  protected $sTitle = 'File system';
  
  public function __construct() {
    
    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');
    
    \Sylma::getControler('dom');
    
    $this->setArguments('../settings.yml');
    
    $dir = $this->getDirectory();
    
    if (!$rights = $this->getArgument('rights')) {
      
      $this->throwException('No default rights defined');
    }
    
    $controler = $this->create('controler', array((string) $dir));
    
    $this->setFiles(array($this->getFile('basic.xml')));
    
    $this->setControler($controler);
  }
}


