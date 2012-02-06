<?php

namespace sylma\parser\action\php\basic;
use \sylma\core, \sylma\parser\action\php;

require_once('core/argumentable.php');
require_once(dirname(__dir__) . '/scope.php');
require_once('Controled.php');

class Condition extends Controled implements core\argumentable, php\scope {

  protected $aContent = array();
  protected $test;
  protected $bInvert = false;

  public function __construct(php\_window $controler, $test, $bInvert = false) {

    $this->setControler($controler);
    $this->test = $test;
    $this->bInvert = $bInvert;
  }

  public function addContent($mVal) {

    if (is_array($mVal)) $this->aContent = array_merge($this->aContent, $mVal);
    else {

      $this->aContent[] = $this->getControler()->create('line', array($this->getControler(), $mVal));
    }
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
       'condition' => array(
          '@invert' => ($this->bInvert ? 'true' : 'false'),
          'test' => $this->test,
          'content' => $this->aContent,
       )
    ));
  }
}