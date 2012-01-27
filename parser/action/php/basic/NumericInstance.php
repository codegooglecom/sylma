<?php

namespace sylma\parser\action\php\basic;
use \sylma\core, \sylma\parser\action\php;

require_once('_Scalar.php');
require_once(dirname(__dir__) . '/_instance.php');

class NumericInstance extends _Scalar implements php\_instance {

  private $mValue = '';
  protected $sFormat = 'php-integer';

  public function __construct($mValue) {

    $this->mValue = $mValue;
  }

  public function asArgument() {

    return $this->createArgument(array(
      'numeric' => $this->mValue,
    ));
  }
}