<?php

namespace sylma\storage\fs;
use \sylma\dom, \sylma\storage\fs, \sylma\storage\fs\controler;

interface directory {
  
  public function __construct($sPath, $sName, array $aRights = array(), fs\directory $parent = null, fs\controler $controler = null);
  public function getDistantFile($aPath, $bDebug = false);
  public function getFullPath();
}