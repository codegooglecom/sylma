<?php

namespace sylma\storage\fs;
use \sylma\dom, sylma\storage\fs;

interface file {
  
  const DEBUG_LOG = 1;
  const DEBUG_EXIST = 2;
  
  public function __construct($sPath, $sName, array $aRights, fs\directory $parent, $iDebug);
  public function getDocument();
  public function doExist($bExist = null);
}