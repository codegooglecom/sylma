<?php

namespace sylma\schema\cached\view;
use sylma\core;

class _String extends Basic {

  protected $sValue;

  public static function format($sValue, array $aSettings) {

    if (isset($aSettings['length'])) {

      $iLength = $aSettings['length'];

      $sValue = mb_strlen($sValue) > $iLength ? mb_substr($sValue, 0, $iLength) . ' ...' : $sValue;
    }

    if (isset($aSettings['nl2br']) && $aSettings['nl2br']) {

      $sValue = nl2br($sValue);
    }

    return $sValue;
  }
}

