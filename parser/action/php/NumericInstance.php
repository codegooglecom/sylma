<?php

namespace sylma\parser\action\php;
use \sylma\core;

require_once('core/argumentable.php');
require_once('core/module/Argumented.php');

class NumericInstance extends core\module\Argumented implements core\argumentable {
  
  private $mValue = '';
  
  public function __construct($mValue) {
    
    $this->mValue = $mValue;
  }
  
  public function asArgument() {
    
    return $this->createArgument(array(
      'numeric' => $this->mValue,
    ));
  }
}