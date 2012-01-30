<?php

namespace sylma\parser\action\php\basic\instance;
use sylma\parser\action\php;

require_once('_Scalar.php');
require_once(dirname(dirname(__dir__)) . '/_instance.php');

class _Null extends _Scalar implements php\_instance {

  protected $sFormat = 'php-null';

  public function __construct(php\_window $controler) {

    $this->setControler($controler);
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array('null' => array()));
  }
}
