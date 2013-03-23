<?php

namespace sylma\parser\languages\php\basic;
use \sylma\core, \sylma\parser\languages\common, \sylma\parser\languages\php;

class _Call extends Called {

  protected $called;

  public function __construct(common\_window $controler, $called, common\_instance $return, array $aArguments = array()) {

    $this->setControler($controler);

    $this->setCalled($called);
    $this->setArguments($this->parseArguments($aArguments));

    $this->setReturn($return);
  }

  public function getCalled() {

    return $this->called;
  }

  public function setCalled($called) {

    if ($called instanceof common\_var) {

      $instance = $called->getInstance();
    }
    else {

      $instance = $called;
      $called = $this->getControler()->addVar($called);
    }

    if (!$instance instanceof _Closure) {

      $this->getControler()->throwException(sprintf('Cannot call %s', \Sylma::show($called)));
    }

    $this->called = $called;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
       'call' => array(
           'called' => $this->getCalled(),
           '#argument' => $this->getArguments(),
       )
    ));
  }
}