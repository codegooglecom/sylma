<?php

namespace sylma\storage\sql\template;
use sylma\core, sylma\template, sylma\schema;

interface pathable extends template\parser\tree  {

  //function getQuery();
  function getNode();

  //function reflectApplyDefault($sPath, array $aPath, $sMode = '');
  //function reflectApplyFunction($sName, array $aPath, $sMode);
  //function reflectApplyAll(array $aPath, $sMode);
}

