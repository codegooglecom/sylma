<?php

namespace sylma\storage\sql\template;
use sylma\core, sylma\template, sylma\schema;

interface pathable extends template\parser\tree  {

  function getNode();

  function reflectApply($sPath, $sMode = '');
  //function reflectApplyPath(array $aPath, $sMode);
  //function reflectApplyFunction($sName, array $aPath, $sMode);
  //function reflectApplyAll(array $aPath, $sMode);
}

