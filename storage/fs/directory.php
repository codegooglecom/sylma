<?php

namespace sylma\storage\fs;
use \sylma\dom, \sylma\storage\fs, \sylma\storage\fs\controler;

interface directory {
  
  public function __construct($sName, fs\directory $parent = null, array $aRights = array(), fs\controler $controler = null);
  public function getDistantFile(array $aPath, $bDebug = false);
  public function getFullPath();
  public function parse();
}