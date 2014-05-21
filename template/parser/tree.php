<?php

namespace sylma\template\parser;
use sylma\core, sylma\dom;

interface tree extends core\tokenable {

  //function isRoot($bVal = null);

  function reflectRead();
  function reflectApply($sMode);
  function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array());
  function reflectApplyDefault($sPath, array $aPath, $sMode, $bRead = false, array $aArguments = array());
  //reflectApplyAll(array $aPath, $sMode, array $aArguments = array())
}

