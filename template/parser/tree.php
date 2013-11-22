<?php

namespace sylma\template\parser;
use sylma\core, sylma\dom;

interface tree extends core\tokenable {

  //function isRoot($bVal = null);

  //function reflectRead($sPath);
  function reflectApply($sMode);
  //function reflectApplyFunction($sName, array $aPath, $sMode)
  //public function reflectApplyDefault($sPath, array $aPath, $sMode)
}

