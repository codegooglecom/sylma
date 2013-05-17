<?php

namespace sylma\modules\tester;
use sylma\core, sylma\dom, sylma\storage\fs, sylma\core\functions;

abstract class Asserter extends core\module\Domed {

  public function assertEqual($val1, $val2) {

    if ($val1 !== $val2) {

      $this->launchException('Values not equal', get_defined_vars());
    }
  }

  public function assertTrue($val) {

    if (!$val) {

      $this->launchException('Value is not TRUE or equivalent');
    }
  }
}