<?php

namespace sylma\parser\action;
use \sylma\core, sylma\parser;

require_once('parser/action.php');
require_once('core/module/Filed.php');

class Controler extends core\module\Filed {
  
  public function __construct() {
    
    //$this->loadDefaultArguments();
    
    $this->setDirectory(__file__);
    $this->setArguments('controler.yml');
    $this->setNamespace(parser\action::NS);
  }
  
  public function getAction($sPath) {
    
    
  }
  
  public function getDirectory() {
    
    return parent::getDirectory();
  }
}