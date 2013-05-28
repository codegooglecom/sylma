<?php

namespace sylma\schema\cached\view;
use sylma\core;

class _String extends Basic {

  protected $sValue;

  public static function format($sValue, array $aSettings) {

    if (isset($aSettings['length'])) {

      $iLength = $aSettings['length'];

      $sValue = strlen($sValue) > $iLength ? substr($sValue, 0, $iLength) . ' ...' : $sValue;
    }

    return $sValue;
  }
}

