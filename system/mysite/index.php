<?php
/*
 * Description
 * Created on 17 oct. 2008
 */

if (!defined("PATH_SEPARATOR")) {
  
  if (strpos($_ENV[ "OS" ], "Win") !== false ) define("PATH_SEPARATOR", ";");
  else define("PATH_SEPARATOR", ":");
}

require('server-config.php'); // no directory yet included
require('system/config.php'); // now default directory inclusion is sylma main directory

if (DEBUG) error_reporting(E_ALL);
else error_reporting(0);

libxml_use_internal_errors(true);

require('Sylma.php');
require(PATH_LIBS.'/Form.php');
require('modules/dbx/DBX.php');

// DB

if (SYLMA_USE_DB) {
  
  require(PATH_LIBS.'/eXist.php');
  require(PATH_LIBS.'/XML_Database.php');
}

ini_set('session.gc_maxlifetime', SESSION_MAX_LIFETIME); 
session_start();

// db::connect();

$sError = set_error_handler("userErrorHandler");

echo Controler::trickMe();
