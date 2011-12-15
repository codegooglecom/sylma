<?php

namespace sylma\parser\action\php;

class Controled {
  
  public function setControler(Window $controler) {
    
    $this->controler = $controler;
  }
  
  public function getControler() {
    
    return $this->controler;
  }
}