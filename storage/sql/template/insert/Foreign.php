<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\storage\sql, sylma\schema\parser, sylma\parser\languages\common;

class Foreign extends sql\template\component\Foreign {

  public function reflectApplyPath(array $aPath, $sMode) {

    parent::reflectApplyPath($aPath, $sMode);
  }
}

