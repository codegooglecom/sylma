<?php

namespace sylma\storage\fs\test;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs;

require_once('modules/tester/Basic.php');

class Editable extends tester\Basic {
  
  const NS = 'http://www.sylma.org/storage/fs/test';
  protected $sTitle = 'File system';
  
  public function __construct() {
    
    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');
    $this->setArguments('../settings.yml');
    
    $this->getControler('dom');
    $user = $this->getControler('user');
    
    $dir = $user->getDirectory('#tmp')->createDirectory();
    //dspm((string) $dir);
    // dspf((string) $dir);
    
    $controler = $this->create('controler');
    $controler->loadDirectory((string) $dir);
    //$controler->setMode('editable');
    //dspf($controler->getDirectory());return;
    $this->setFiles(array($this->getFile('editable.xml')));
    
    $this->setControler($controler);
  }
}


