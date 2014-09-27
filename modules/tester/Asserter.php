<?php

namespace sylma\modules\tester;
use sylma\core;

abstract class Asserter extends core\module\Domed {

  protected $iCount = 1;

  protected function resetCount() {

    $this->iCount = 1;
  }

  /**
   * @deprecated use assertEquals() instead
   */
  public function assertEqual($val1, $val2) {

    return $this->assertEquals($val1, $val2);
  }

  public function assertEquals($val1, $val2) {

    if ($val1 !== $val2) {

      $sPost = '';

      if (is_string($val1) && is_string($val2)) {

        $sPost = $this->findDiff($val1, $val2);
      }

      $this->launchException("Values not equal in  '{$this->getCount()}'" . $sPost, get_defined_vars());
    }

    $this->updateCount();
  }

  protected function findDiff($sVal1, $sVal2) {

    $iStart = 5;
    $iEnd = 11;

    $iDiff = strspn($sVal1 ^ $sVal2, "\0");
    $sVal1 = mb_check_encoding($sVal1) ? mb_substr($sVal1, $iDiff - $iStart, $iEnd) : substr($sVal1, $iDiff - $iStart, $iEnd);
    $sVal2 = mb_check_encoding($sVal2) ? mb_substr($sVal2, $iDiff - $iStart, $iEnd) : substr($sVal2, $iDiff - $iStart, $iEnd);


    return ' at char. ' . $iDiff . ' in the middle of : "' . $sVal1 . '"' . ', expecting : "' . $sVal2 . '"';
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