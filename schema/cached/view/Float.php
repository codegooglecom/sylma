<?php

namespace sylma\schema\cached\view;
use sylma\core;

class Float extends Numeric {

  protected $sValue;

  public static function format($sValue, array $aSettings) {

    if ($sValue) {

      if ($aSettings) {

        $iCount = current($aSettings);
      }
      else {

        $iCount = 0;
      }

      $sValue = round($sValue, $iCount);
    }

    return $sValue;
  }
}

