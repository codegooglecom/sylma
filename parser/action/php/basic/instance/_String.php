<?php

namespace sylma\parser\action\php\basic\instance;
use sylma\core, sylma\parser\action\php;

require_once('_Scalar.php');
require_once(dirname(dirname(__dir__)) . '/_instance.php');

class _String extends _Scalar implements php\_instance {

  private $sValue = '';
  protected $sFormat = 'php-string';

  public function __construct(php\_window $controler, $sValue = '') {

    $this->sValue = $sValue;
    $this->setControler($controler);
  }

  public function asArgument() {

    return $this->createArgument(array(
      'string' => $this->sValue,
    ));
  }
}