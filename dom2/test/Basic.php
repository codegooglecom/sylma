<?php

namespace sylma\dom\test;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs;

require_once('modules/tester/Basic.php');

class Basic extends tester\Basic {
  
  const NS = 'http://www.sylma.org/dom/test';
  protected $sTitle = 'DOM';
  
  public function __construct() {
    
    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');
    
    $this->setArguments('settings.yml');
    
    $this->setFiles(array($this->getFile('basic.xml')));
    $this->setControler($this);
  }
  
  public function getFile($sPath, $bDebug = true) {
    
    return parent::getFile($sPath, $bDebug);
  }
}

