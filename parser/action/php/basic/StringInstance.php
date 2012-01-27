<?php

namespace sylma\parser\action\php\basic;
use \sylma\core, \sylma\parser\action\php;

require_once('_Scalar.php');
require_once(dirname(__dir__) . '/_instance.php');

class StringInstance extends _Scalar implements php\_instance {

  private $sValue = '';
  protected $sFormat = 'php-integer';

  public function __construct($sValue = '') {

    $this->sValue = $sValue;
  }

  public function asArgument() {

    return $this->createArgument(array(
      'string' => $this->sValue,
    ));
  }
}