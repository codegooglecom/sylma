<?php

namespace sylma\core\argument\test;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs;

require_once('modules/tester/Prepare.php');

class Basic extends tester\Prepare {
  
  const NS = 'http://www.sylma.org/core/argument/test';
  protected $sTitle = 'Arguments';
  
  public function __construct() {
    
    $this->getControler('dom');
    
    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');
    
    $this->setArguments('settings.yml');
    
    $controler = $this->create('controler', array($this->getDirectory('samples')));
    $controler->setArguments($this->getArguments());
    
    $this->setControler($controler);
  }
  
  protected function loadDocument(dom\handler $doc, fs\file $file) {
    
    if ($doc->getRoot() && ($sClass = $doc->getRoot()->readAttribute('class', $this->getNamespace(), false))) {
      
       $this->getControler()->setArgument('class-alias', $sClass);
    }
    
    return parent::loadDocument($doc, $file);
  }
}

