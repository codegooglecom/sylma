<?php

namespace sylma\parser\languages\php\basic\instance;
use sylma\core, sylma\parser\languages\common, sylma\parser\languages\php;

require_once('_Scalar.php');
require_once('parser/languages/common/_instance.php');

class _String extends _Scalar implements common\_instance {

  private $sValue = '';
  protected $sFormat = 'php-string';

  public function __construct(common\_window $controler, $sValue = '') {

    $this->sValue = $sValue;
    $this->setControler($controler);
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'string' => $this->sValue,
    ));
  }
}