<?php

namespace sylma\storage\fs;
use \sylma\dom, sylma\storage\fs;

interface file {
  
  const DEBUG_LOG = 1;
  const DEBUG_EXIST = 2;
  
  function __construct($sName, fs\directory $parent, array $aRights, $iDebug);
  function getDocument();
  function doExist($bExist = null);
  function parse();
  function __toString();
}
