<?php

namespace sylma\parser\action\php\basic\instance;
use \sylma\core, \sylma\parser\action\php;

require_once('_Scalar.php');
require_once(dirname(dirname(__dir__)) . '/_instance.php');

class _Numeric extends _Scalar implements php\_instance {

  private $mValue = '';
  protected $sFormat = 'php-integer';

  public function __construct(php\_window $controler, $mValue) {

    $this->setControler($controler);
    $this->mValue = $mValue;
  }

  public function asArgument() {

    return $this->createArgument(array(
      'numeric' => $this->mValue,
    ));
  }
}