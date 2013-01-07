<?php

namespace sylma\parser\languages\php\basic;
use \sylma\core, \sylma\parser\languages\common, \sylma\parser\languages\php;

class _Closure extends common\basic\Window implements common\scope, common\argumentable, common\_instance, common\_object {

  protected $aArguments = array();
  protected $return;

  public function __construct(common\_window $controler, array $aArguments = array()) {

    $this->setControler($controler);
    $this->loadArguments($aArguments);
  }

  protected function loadArguments(array $aArguments) {

    foreach ($aArguments as $var) {

      $var->isStatic(true);
      $this->setVariable($var);
    }
  }

  public function addContent($mVar) {

    if (is_array($mVar)) {

      foreach ($mVar as $mSub) {

        $this->addContent($mSub);
      }
    }
    else {

      $this->aContent[] = $this->getControler()->create('line', array($this->getControler(), $mVar));
      $this->setReturn($mVar);
    }

    return $this->getReturn();
  }

  protected function setReturn($mVar) {

    $this->return = $mVar;
  }

  protected function getContent() {

    return $this->aContent;
  }

  protected function getArguments() {

    return $this->aVariables;
  }

  public function getInterface() {

    return $this->getReturn();
  }

  public function getReturn() {

    return $this->return;
  }

  public function asArgument() {

    $aContent = $this->getContent();
    $return = array_pop($aContent);

    return $this->getControler()->createArgument(array(
       'closure' => array(
         '#argument' => $this->getArguments(),
         'content' => array(
           $aContent,
           'return' => array($return),
         ),
       ),
    ));
  }
}