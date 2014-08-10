<?php

namespace sylma\schema\cached\view;
use sylma\core;

class Numeric extends Basic {

  protected $sValue;

  public static function format($sValue, array $aSettings) {

    if ($sValue) {

      $sValue = number_format($sValue, 0, ".", "'");
    }

    return $sValue;
  }
}

