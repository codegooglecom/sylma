<?php

if (!defined("SYLMA_PATH_SEPARATOR")) {
  
  if (strpos($_ENV[ "OS" ], "Win") !== false ) define("SYLMA_PATH_SEPARATOR", ";");
  else define("SYLMA_PATH_SEPARATOR", ":");
}

require_once('system/config-sylma.php'); 
require_once('system/config.php'); 
require('core/Sylma.php');

