<?php

namespace sylma\modules\tester;
use sylma\core, sylma\dom, sylma\storage\fs, sylma\core\functions;

abstract class Asserter extends core\module\Domed {

  protected $iCount = 1;

  protected function resetCount() {

    $this->iCount = 1;
  }

  public function assertEqual($val1, $val2) {

    if ($val1 !== $val2) {

      $this->launchException("Values not equal in '{$this->getCount()}'", get_defined_vars());
    }

    $this->updateCount();
  }

  protected function updateCount() {

    $this->iCount++;
  }

  protected function getCount() {

    return $this->iCount;
  }

  public function assertTrue($val) {

    if (!$val) {

      $this->launchException("Value is not TRUE or equivalent in '{$this->getCount()}'", get_defined_vars());
    }

    $this->updateCount();
  }
}