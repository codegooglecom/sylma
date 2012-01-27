<?php

namespace sylma\parser\action\php\basic;
use \sylma\core, \sylma\parser\action\php;

require_once('_Var.php');
require_once(dirname(__dir__) . '/_scalar.php');

class _ScalarVar extends _Var implements php\_scalar {

  public function useFormat($sFormat) {

    return $this->getInstance()->useFormat($sFormat);
  }
}