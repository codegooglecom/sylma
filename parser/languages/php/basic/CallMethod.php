<?php

namespace sylma\parser\languages\php\basic;
use \sylma\core, \sylma\parser\languages\common, \sylma\parser\languages\php;

require_once('Called.php');
require_once('core/argumentable.php');

class CallMethod extends Called  {

  private $called;

  public function __construct(common\_window $controler, $called, $sMethod, common\_instance $return, array $aArguments = array()) {

    $this->setCalled($called);
    $this->setName($sMethod);
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

      $this->throwException(sprintf('Cannot call object of type %s', $this->show($called)));
    }
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'call' => array(
          '@name' => $this->getName(),
          'called' => $this->called,
          '#argument' => $this->getArguments(),
      ),
    ));
  }
}