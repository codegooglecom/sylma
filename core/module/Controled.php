<?php

namespace sylma\core\module;
use \sylma\core;

require_once('core/controled.php');
require_once('Exceptionable.php');

abstract class Controled extends Exceptionable implements core\controled {
  
  protected $controler;
  
  public function setControler(core\factory $controler) {
    
    $this->controler = $controler;
  }
  
  public function getControler() {
    
    if (!$this->controler) {
      
      $this->throwException('No controler defined');
    }
    
    return $this->controler;
  }
}