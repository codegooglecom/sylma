<?php

namespace sylma\storage\fs;
use \sylma\dom, \sylma\storage\fs, \sylma\storage\fs\controler, \sylma\core;

require_once('resource.php');

interface directory extends fs\resource {
  
  public function __construct($sName, fs\directory $parent = null, array $aRights = array(), fs\controler $controler = null);
  public function getDistantFile(array $aPath, $bDebug = false);
}