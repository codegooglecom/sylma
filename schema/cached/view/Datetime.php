<?php

namespace sylma\schema\cached\view;
use sylma\core;

class Datetime extends _String {

  const FORMAT = '%d.%m.%Y';

  public static function format($sValue, array $aSettings) {

    if (isset($aSettings['pattern'])) {

      $sFormat = $aSettings['pattern'];
    }
    else {

      $sFormat = self::FORMAT;
    }

    $date = new \DateTime($sValue);

    if ($date->getTimestamp() < 0) {
/*
      $e = new core\exception\Basic('Bad datetime');
      $e->save(false);
 */
    }

    return strftime($sFormat, $date->getTimestamp());
  }
}

