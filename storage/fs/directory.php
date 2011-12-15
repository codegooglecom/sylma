<?php

namespace sylma\storage\fs;
use \sylma\dom, \sylma\storage\fs, \sylma\storage\fs\controler, \sylma\core;

require_once('resource.php');
require_once('core/tokenable.php');
require_once('core/argumentable.php');

interface directory extends fs\resource, core\argumentable, core\tokenable {
  
  public function __construct($sName, fs\directory $parent = null, array $aRights = array(), fs\controler $controler = null);
  public function getDistantFile(array $aPath, $bDebug = false);
}