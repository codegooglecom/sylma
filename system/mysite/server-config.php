<?php

/**
 * This array will override $sylma from system/config-sylma.php see it for more details. It let you customize these values.
 * This is only a sample of what can be usefull. None of these values are required.
 * Be careful to reset to original values for production installation
 */


$sylmaSite = array(
  
  'debug' => array(
    'enable' => false, // DEFAULT = false
  ),
  
  'messages' => array(
    
    'format' => array(
      'enable' => false, // DEFAULT = true
    ),
    'backtrace' => array(
      'enable' => false, // DEFAULT = true
      'count' => 3, // DEFAULT = 3
    ),
  ),
  
  'form' => array(
    'redirect' => '.redirect',
  ),
  
  'users' => array(
    'server' => array(
      'ip' => '127.0.1.1',
    ),
  ),
  
  'maintenance' => array(
    'enable' => false, // DEFAULT = false
    'file' => 'protected/maintenance.html',
  ),
  
  'db' => array(
    'enable' => false, // switch to TRUE to enable database
    'user' => 'username',
    'password' => 'password',
    'collection' => '/mysite',
    'namespace' => 'http://www.example.com',
    'debug' => array(
      'show-queries' => false,
      'show-results' => false,
    ),
  ),
  
  'actions' => array(
    'redirect' => array(
      'enable' => true,
    ),
    'stats' => array(
      'enable' => true,
    ),
  ),
  
  'modules' => array(
    
    'editor' => array(
      'path' => '/modules/editeur',
    ),
  ),
);

// The constants are currently required

define('MAIN_DIRECTORY', 'protected');
define('SYLMA_PATH', MAIN_DIRECTORY.'/sylma');
define('SYLMA_PATH_SETTINGS',   '/config/root.xml');

set_include_path(get_include_path() . SYLMA_PATH_SEPARATOR . SYLMA_PATH);


