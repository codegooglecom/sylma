<?php

namespace sylma\parser\languages\php\basic\instance;
use sylma\parser\languages\php, sylma\parser\languages\common;

require_once('_Scalar.php');
require_once('parser/languages/common/_instance.php');

class _Null extends _Scalar implements common\_instance {

  protected $sFormat = 'php-null';

  public function __construct(common\_window $controler) {

    $this->setControler($controler);
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array('null' => array()));
  }
}
