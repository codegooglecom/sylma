<?php

namespace sylma\parser\action\php\basic;
use \sylma\core;

require_once('core/argumentable.php');
require_once('Controled.php');

class Assign extends Controled implements core\argumentable {
  
  protected $variable;
  protected $value;
  
  public function __construct(Window $controler, _Var $variable, $value) {
    
    $this->variable = $variable;
    $this->value = $value;
    $this->setControler($controler);
  }
  
  public function asArgument() {
    
    return $this->getControler()->createArgument(array(
      'assign' => array(
        'variable' => $this->variable,
        'value' => $this->value,
      )));
  }
}