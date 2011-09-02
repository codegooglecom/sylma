<?php

namespace sylma\core\argument\test;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs;

require_once('modules/tester/Prepare.php');

class Basic extends tester\Prepare {
  
  const NS = 'http://www.sylma.org/core/argument/test';
  protected $sTitle = 'Arguments';
  
  public function __construct() {
    
    $this->setDirectory(__file__);
    $this->setNamespaces(array('self' => self::NS));
    $this->setArguments('settings.yml');
    
    $controler = $this->create('controler');
    $controler->setArguments($this->getArguments());
    
    $this->setControler($controler);
  }
  
  protected function test(dom\element $test, $controler, dom\document $doc, fs\file $file) {
    
    $controler->setDirectory($file->getParent());
    
    return parent::test($test, $controler, $doc, $file);
  }
}

