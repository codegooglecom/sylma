<?php

/**
 * $sylma is the main global variable to set global config values in a structured array trees
 * This array can be override with @function array_merge_keys() defined below
 * An array is necessary for process appending before xml lib is usable (TODO details)
 * In a second time, $sylma may be merged into root.xml settings for use with the xml lib
 */
$sylma = array(
  
  'modules' => array(
    
    'editor' => array(
      'path' => '/modules/editeur',
    ),
  ),
  
  'users' => array(
    'root' => array(
      //'name' => 'root',
      //'groups' => array('0', 'users'),
      'error-level' => E_ALL, // or E_ALL ^ E_WARNING ^ E_NOTICE
    ),
    'server' => array( // server user for cron jobs
      'name' => 'server', // 'server' user is now reserved
      'ip' => '', // localhost cannot always be used when multiple domains share same ip
      'groups' => array('famous', 'server'), // same as anonymouse
      'arguments' => array(),
    ),
    'anonymouse' => array(
      'name' => 'anonymouse', // 'anonymouse' user is now reserved
      'groups' => array('famous'),
      'arguments' => array('full-name' => 'Anonymous')
    ),
    'authenticated' => array(
      'groups' => array('users'),
    ),
  ),
  
  'maintenance' => array(
    'enable' => false, // DEFAULT = false
    'file' => '', // TODO build the html page
    'login' => 'protected/sylma/modules/users/maintenance-login.html',
    'login-do' => 'sylma/modules/users/interface/login_do.redirect',
  ),
  
  'db' => array(
    'enable' => false, // switch to TRUE to enable database
    'host' => 'http://localhost:8080/exist/services/Query?wsdl',
    'user' => 'myuser',
    'password' => 'mypass',
    'collection' => '/mycollection',
    'namespace' => 'http://www.example.com',
    'debug' => array(
      'show-queries' => false, // DEFAULT = false
      'show-results' => false, // DEFAULT = false
    ),
  ),
  
  'directories' => array(
    'root' => array(
      'rights' => array('owner' => 'root', 'group' => '0', 'mode' => '700', 'user-mode' => null),
    ),
  ),
  
  'cookies' => array(
    'lifetime' => 3600 * 8,
  ),
  
  'session' => array(
    'lifetime' => (string) 3600 * 8,
  ),
  
  'xml' => array(
    'rights' => array(
      'enable' => true, // DEFAULT = true
    ),
    'encoding' => array(
      'check' => true,
    ),
  ),
  
  'form' => array(
    'redirect' => '.redirect',
  ),
  
  'messages' => array(
    'print' => false, // DEFAULT = false
    'format' => array(
      'enable' => true, // DEFAULT = true
    ),
    'backtrace' => array(
      'enable' => true, // DEFAULT = true
      'count' => 3, // DEFAULT = 3
    ),
    'xml' => array(
      'enable' => true, // DEFAULT = true, WARNING : with false can cause UTF-8 errors - TODO
    ),
    'log' => array(
      'enable' => true, // DEFAULT = false
    ),
    'rights' => array(
      'enable' => true, // DEFAULT = true
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
);



