<?php

namespace sylma\template\binder;
use sylma\core;

class Cached extends core\argument\Readable {

  protected function parsePath($sPath) {

    if (strpos($sPath, '.') !== false) $aResult = explode('.', $sPath);
    else $aResult = array($sPath);

    return $aResult;
  }
}

