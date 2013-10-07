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

      $sPost = '';

      if (is_string($val1) && is_string($val2)) {

        $sPost = $this->findDiff($val1, $val2);
      }

      $this->launchException("Values not equal in '{$this->getCount()}'" . $sPost, get_defined_vars());
    }

    $this->updateCount();
  }

  protected function findDiff($sVal1, $sVal2) {

    $iDiff = strspn($sVal1 ^ $sVal2, "\0");
    return ' at char. ' . $iDiff . ' in the middle of : "' . mb_substr($sVal2, $iDiff - 5, 11) . '"' . ', expecting : "' . mb_substr($sVal1, $iDiff - 5, 11) . '"';
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