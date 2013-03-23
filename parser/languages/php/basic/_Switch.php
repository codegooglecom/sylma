<?php

namespace sylma\parser\languages\php\basic;
use sylma\core, sylma\parser\languages\common, sylma\dom;

class _Switch extends common\basic\Controled implements common\argumentable, common\structure {

  protected $aCases = array();
  protected $test;

  public function __construct(common\_window $controler, $test) {

    $this->setControler($controler);
    $this->setTest($test);
  }

  public function addCase($sName, $content) {

    if (!isset($this->aCases[$sName])) $this->aCases[$sName] = $this->createCase($sName, $content);
    else $this->aCases[$sName]->addContent($content);
  }

  protected function createCase($sName, $content = null) {

    return $this->getControler()->createCase($sName, $content);
  }

  public function getTest() {

    return $this->test;
  }

  public function setTest($test) {

    $this->test = $test;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
       'switch' => array(
         'test' => $this->test,
         array_values($this->aCases),
       )
    ));
  }
}