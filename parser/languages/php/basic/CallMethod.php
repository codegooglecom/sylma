<?php

namespace sylma\parser\languages\php\basic;
use \sylma\core, \sylma\parser\languages\common, \sylma\parser\languages\php;

class CallMethod extends Called implements common\addable, common\callable  {

  private $called;
  protected $bStatic = false;

  public function __construct(common\_window $controler, $called, $sMethod, common\_instance $return, array $aArguments = array()) {

    $this->setControler($controler);

    $this->setCalled($called);
    $this->checkCalled();

    $this->setName($sMethod);

    $this->setReturn($return);

    $this->setArguments($this->parseArguments($aArguments));
  }

  protected function getCalled() {

    return $this->called;
  }

  protected function setCalled($called) {

    $this->called = $called;
  }

  protected function checkCalled() {

    $called = $this->getCalled();

    if ($called instanceof self || $called instanceof common\_object) {

      if ($called instanceof php\basic\instance\_Class) {

        $this->isStatic(true);
      }
    }
    else {

      $this->getControler()->throwException(sprintf('Cannot call object of type %s', \Sylma::show($called)));
    }
  }

  public function call($sMethod, array $aArguments = array(), $mReturn = null, $bVar = false) {

    $call = $this->getControler()->createCall($this, $sMethod, $mReturn, $aArguments);

    return $bVar ? $call->getVar() : $call;
  }

  public function insert() {

    $this->getWindow()->add($this);
  }

  public function isStatic($bStatic = null) {

    if (!is_null($bStatic)) $this->bStatic = $bStatic;
    return $this->bStatic;
  }

  public function onAdd() {

    $window = $this->getWindow();

    $window->loadContent($this->getCalled());
    $window->loadContent($this->getArguments());
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'call-method' => array(
          '@name' => $this->getName(),
          '@static' => $this->isStatic() ? true : null,
          'called' => $this->getCalled(),
          '#argument' => $this->getArguments(),
      ),
    ));
  }
}