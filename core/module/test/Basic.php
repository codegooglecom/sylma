<?php

namespace sylma\core\module\test;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs;

require_once('modules/tester/Prepare.php');

class Basic extends tester\Prepare {
  
  const NS = 'http://www.sylma.org/core/module/test';
  protected $sTitle = 'Module';
  
  public function __construct() {
    
    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');
    
    $this->setArguments('settings.yml');
    
    $this->setFiles(array($this->getFile('filed.xml'), $this->getFile('domed.xml')));
    $this->setControler($this);
  }
  
  public function getArgument($sPath, $mDefault = null, $bDebug = false) {
    
    return parent::getArgument($sPath, $mDefault, $bDebug);
  }
  
  public function setArgument($sPath, $mValue) {
    
    return parent::setArgument($sPath, $mValue);
  }
}

