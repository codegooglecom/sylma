<?php

if (!defined("SYLMA_PATH_SEPARATOR")) {
  
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') define("SYLMA_PATH_SEPARATOR", ";");
  else define("SYLMA_PATH_SEPARATOR", ":");
}

require_once('system/config-sylma.php'); 
require_once('system/config.php'); 
require('core/Sylma.php');

