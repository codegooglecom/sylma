<?php

/* Debug */ // be careful to reset to original values for production installation

define('DEBUG', false); // DEFAULT = false

// To add customized settings to $sylma, see [sylma]/system/config-sylma.php for more details
$sylma = array_merge_keys($sylma, array(
  
  'users' => array(
    'server' => array( // server user for cron jobs
      'ip' => '127.0.0.1', // localhost cannot always be used when multiple domains share same ip
    ),
  ),
  
  'maintenance' => array(
    'enable' => null, // DEFAULT = null, warning 'false' will be see as 'true'
    'file' => 'protected/maintenance.html',
  ),
  
  'db' => array(
    'enable' => false, // switch to TRUE to enable database
    'user' => 'username',
    'password' => 'password',
    'collection' => '/mysite',
    'namespace' => 'http://www.example.com',
  ),
));

define('SYLMA_DISABLE_RIGHTS', false); // DEFAULT = false
define('SYLMA_DISABLE_STATUTS', false); // messages statuts, DEFAULT = false

// Messages

define('SYLMA_PRINT_MESSAGES', false); // DEFAULT = false
define('SYLMA_MESSAGES_BACKTRACE', true); // DEFAULT = true
define('SYLMA_BACKTRACE_LIMIT', 3); // DEFAULT = 3
define('MESSAGES_SHOW_XML', true); // DEFAULT = true, WARNING : with false can cause UTF-8 errors - TODO
define('SYLMA_LOG_MESSAGES', false); // DEFAULT = false
define('FORMAT_MESSAGES', true); // DEFAULT = true

define('SYLMA_DB_SHOW_QUERIES', false); // DEFAULT = false
define('SYLMA_DB_SHOW_RESULTS', false); // DEFAULT = false

define('SYLMA_USE_DB', true);

/* Global */ // Could be different for production or test server

define('SYLMA_HOST_NAME', 'Undefined');

define('PATH_SYLMA', 'protected/sylma');
define('PATH_LIBS', PATH_SYLMA.'/lib');
define('PATH_PHP', 'protected');
define('MAIN_DIRECTORY', PATH_PHP);
define('SESSION_MAX_LIFETIME', 3600 * 8);
define('ERROR_LEVEL', E_ALL); // or E_ALL ^ E_WARNING ^ E_NOTICE
define('SYLMA_PATH_SETTINGS',   '/config/root.xml');

define('SYLMA_ADMIN_EXTENSION',   '.sylma');
define('SYLMA_FORM_REDIRECT_EXTENSION',   '.redirect');

define('SYLMA_RESULT_LIFETIME', 30); // seconds, JS results stored in the $_SESSION, read in AJAX
define('SYLMA_ENCODING_CHECK', true);

define('SYLMA_ACTION_STATS', true); // infos
define('SYLMA_ACTION_ERROR_REDIRECT', false);

$aDB = array(


set_include_path(get_include_path() . PATH_SEPARATOR . PATH_SYLMA . PATH_SEPARATOR . PATH_PHP);

