<?php

namespace sylma\parser\action;
use \sylma\core;

require_once('core/argumentable.php');
require_once('core/module/Argumented.php');

class Template extends core\module\Argumented implements core\argumentable {
  
  private $mValue = '';
  
  public function asArgument() {
    
    return $this->createArgument(array(
      'template' => $this->mValue,
    ));
  }
}