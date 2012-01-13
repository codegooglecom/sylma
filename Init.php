<?php

if (!defined("SYLMA_PATH_SEPARATOR")) {
  
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') define("SYLMA_PATH_SEPARATOR", ";");
  else define("SYLMA_PATH_SEPARATOR", ":");
}

preg_match('`[^/]+/[^/]+$`', str_replace('\\', '/', dirname(__FILE__)), $result);

// only used to set include path
define('SYLMA_PATH', $result[0]); // ex : protected/sylma
set_include_path(get_include_path() . SYLMA_PATH_SEPARATOR . SYLMA_PATH);

define('SYLMA_PROTECTED_PATH', substr($result[0], strlen(SYLMA_ROOT))); // ex : /sylma

require_once('system/config.php'); 
require('core/Sylma.php');

