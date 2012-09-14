<?php

namespace sylma\parser\languages\php\basic;
use \sylma\core, \sylma\parser\languages\common, \sylma\parser\languages\php;

require_once('Called.php');
require_once('core/argumentable.php');

class CallMethod extends Called  {

  private $called;

  public function __construct(Window $controler, common\_object $called, $sMethod, common\_instance $return, array $aArguments = array()) {

    $this->called = $called;
    $this->setName($sMethod);
    $this->setControler($controler);
    $this->setReturn($return);
//dspf($aArguments, 'error');
    $this->setArguments($this->parseArguments($aArguments));
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