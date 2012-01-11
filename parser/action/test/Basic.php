<?php

namespace sylma\parser\action\test;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs, \sylma\parser;

require_once('modules/tester/Basic.php');

class Basic extends tester\Basic {
  
  const NS = 'http://www.sylma.org/parser/action/test';
  const FS_CONTROLER = 'fs/editable';
  
  protected $sTitle = 'Action';
  
  public function __construct(parser\action\Controler $controler = null) {
    
    \Sylma::getControler('dom');
    
    require_once(dirname(dirname(__dir__)) . '/action.php');
    
    $this->setDirectory(__file__);
    $this->setNamespaces(array(
        'self' => self::NS,
        'le' => parser\action::NS,
    ));
    
    if (!$controler) $controler = \Sylma::getControler('action');
    
    $this->setControler($controler);
  }
  
  protected function setArgument($sPath, $mValue) {
    
    return parent::setArgument($sPath, $mValue);
  }
  
  protected function test(dom\element $test, $controler, dom\document $doc, fs\file $file) {
    
    if (!$node = $test->get('le:action')) {
      
      $this->throwException(txt('Test must build an action'));
    }
    
    $fs = $this->getControler('fs');
    
    $tmp = $fs->getDirectory((string) $this->getDirectory())->addDirectory('#tmp');
    $dir = $tmp->createDirectory();
    
    $action = $controler->buildAction($this->createDocument($node), array(), $dir, $file->getParent());
    $this->setArgument('action', $action->asDOM());
    
    $result = parent::test($test, $this, $doc, $file);
    
    $dir->delete();
    
    return $result;
  }
}

