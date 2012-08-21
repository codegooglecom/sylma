<?php

namespace sylma\parser\languages\php\basic\instance;
use \sylma\core, \sylma\parser\languages\common, \sylma\parser\languages\php;

require_once('_Scalar.php');
require_once('parser/languages/common/_instance.php');

class _Numeric extends _Scalar implements common\_instance {

  private $mValue = '';
  protected $sFormat = 'php-integer';

  public function __construct(common\_window $controler, $mValue) {

    $this->setControler($controler);
    $this->mValue = $mValue;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'numeric' => $this->mValue,
    ));
  }
}