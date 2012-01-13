<?php

namespace sylma\storage\fs\test;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs;

require_once('modules/tester/Basic.php');

class Editable extends tester\Basic {
  
  const NS = 'http://www.sylma.org/storage/fs/test';
  const FS_CONTROLER = 'fs/editable';
  
  protected $sTitle = 'File update';
  
  /**
   * @var fs\directory
   */
  protected $tmp;
  
  public function __construct() {
    
    $this->getControler('dom');
    
    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');
    $this->setArguments('../settings.yml');
    
    $user = $this->getControler('user');
    //dspf($this->getDirectory((string) $user->getDirectory()));
    //$this->throwException('t');
    $dir = $this->getDirectory((string) $user->getDirectory())->addDirectory('#tmp')->createDirectory();
    $this->tmp = $dir;
    
    $controler = $this->create('controler', array('', true));
    $controler->loadDirectory((string) $dir);
    
    $this->setFiles(array($this->getFile('editable.xml')));
    
    $this->setControler($controler);
  }
  
  protected function onFinish() {
    
    if ($this->tmp) $this->tmp->delete();
  }
}


