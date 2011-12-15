<?php

namespace sylma\parser\action\php;
use \sylma\core;

require_once('core/argumentable.php');
require_once('core/module/Argumented.php');

class StringInstance extends core\module\Argumented implements core\argumentable {
  
  private $sValue = '';
  
  public function __construct($sValue) {
    
    $this->sValue = $sValue;
  }
  
  public function asArgument() {
    
    return $this->createArgument(array(
      'string' => $this->sValue,
    ));
  }
}