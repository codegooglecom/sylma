<?php

namespace sylma\parser\action\php\basic;
use \sylma\core, \sylma\parser\action\php;

require_once('core/argumentable.php');
require_once(dirname(__dir__) . '/_scalar.php');
require_once(dirname(__dir__) . '/_instance.php');
require_once('core/module/Argumented.php');

class StringInstance extends core\module\Argumented implements core\argumentable, php\_scalar, php\_instance {

  private $sValue = '';

  public function __construct($sValue = '') {

    $this->sValue = $sValue;
  }

  public function asArgument() {

    return $this->createArgument(array(
      'string' => $this->sValue,
    ));
  }
}