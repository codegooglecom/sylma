<?php

namespace sylma\storage\sql\template\hollow;
use sylma\core, sylma\storage\sql, sylma\schema\parser, sylma\parser\languages\common;

class Field extends sql\template\component\Field {

  public function reflectApply($sPath, $sMode = '') {

    if ($sPath) {

      $result = parent::reflectApply($sPath, $sMode);
    }
    else {

      $result = null;
    }

    return $result;
  }

  public function reflectRead() {

    return null;
  }
}

