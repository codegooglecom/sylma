<?php

namespace sylma\parser\languages\common\basic;
use \sylma\core, \sylma\parser\languages\common, \sylma\parser\languages\php;

abstract class _Call extends Controled implements common\argumentable  {

  protected $sName;

  protected $return;

  protected $aArguments;

  protected $var;

  protected $called;

  public function __construct(common\_window $controler, $called, array $aArguments = array(), common\ghost $return = null) {

    $this->setCalled($called);
    $this->setName($sName);
    $this->setControler($controler);
    $this->setReturn($return);
//dspf($aArguments, 'error');
    $this->setArguments($this->parseArguments($aArguments));
  }

  protected function setCalled($called) {

    if ($called instanceof self || $called instanceof common\_object) {

      $this->called = $called;
    }
    else {

      $this->throwException(sprintf('Cannot call non object %s', $this->show($called)));
    }
  }

  /**
   * @return array
   */
  public function getArguments() {

    return $this->aArguments;
  }

  public function setArguments(array $aArguments) {

    $this->aArguments = $aArguments;
  }

  public function getReturn() {

    return $this->return;
  }

  protected function setReturn(common\_instance $return) {

    $this->return = $return;
  }

  protected function parseArguments($aArguments) {

    $window = $this->getControler();
    $aResult = array();

    foreach ($aArguments as $mVar) {

      $arg = $window->argToInstance($mVar);

      if ($arg instanceof common\basic\_Object) {

        $this->getControler()->throwException('Cannot add object instance here');
      }

      $aResult[] = $arg;
    }

    return $aResult;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'call' => array(
        '@name' => $this->getName(),
        'called' => $this->called,
      ),
    ));
  }

}
