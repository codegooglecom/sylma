<?php

namespace sylma\parser\action\php\basic;
use \sylma\core, \sylma\parser\action\php;

require_once('core/argumentable.php');
require_once(dirname(__dir__) . '/_scope.php');
require_once('Controled.php');

class _Condition extends Controled implements core\argumentable, php\_scope {
  
  protected $aContent = array();
  protected $test;
  
  public function __construct(php\_window $controler, php\_Compare $test) {
    
    $this->setControler($controler);
    
    $this->test = $test;
    $this->sName = $sName;
  }
  
  public function addContent($mVal) {
    
    if (is_array($mVal)) $this->aContent = array_merge($this->aContent, $mVal);
    else $this->aContent[] = $mVal;
  }
  
  public function asArgument() {
    
    return $this->getControler()->createArgument(array(
       'condition' => array(
           'test' => $this->sName,
           $this->aContent,
       )
    ));
  }
}