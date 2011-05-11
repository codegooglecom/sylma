<?php

/**
 * $sylma is the main global variable to set global config values in a structured array trees
 * This array can be override with @function array_merge_keys() defined below
 * An array is necessary for process appending before xml lib is usable (TODO details)
 * In a second time, $sylma may be merged into root.xml settings for use with the xml lib
 */
$sylma = array(
  
  'modules' => array(
    
    'users' => array(
      'path' => '/sylma/modules/users',
    ),
    
    'editor' => array(
      'path' => '/modules/editeur',
    ),
  ),
  
  'users' => array(
    
    'classes' => array(
      'user' => array(
        'name' => 'XUser',
        'file' => '/sylma/modules/users/XUser.php',
      ),
      'cookie' => array(
        'name' => 'Cookie',
        'file' => '/sylma/user/Cookie.php',
      ),
    ),
    
    'path' => '/users',
    'profil' => 'profil.xml',
    
    'cookies' => array(
      'name' => 'sylma-user',
      'secret-key' => 'This value is not really secret', // (!) This value should be rewrited for a good cookie security
      'lifetime' => array(
        'short' => 3600 * 8, // 8h
        'normal' => 3600 * 8 * 14, //14j
      ),
    ),
    
    'session' => array(
      'name' => 'sylma-user',
      'lifetime' => (string) 3600 * 8,
    ),
    
    'root' => array(
      //'name' => 'root',
      //'groups' => array('0', 'users'),
      'error-level' => E_ALL, // or E_ALL ^ E_WARNING ^ E_NOTICE
      'arguments' => array(
        // 'db' => array( // to set specific credentials to a user
          // 'user' => 'rootuser',
          // 'password' => 'rootpass',
        ),
      // ),
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
      'arguments' => array('full-name' => 'Anonymous'),
    ),
    'authenticated' => array(
      'groups' => array('users'),
      'arguments' => array(
        // 'db' => array( // to set specific credentials to a authentified users
          // 'user' => 'authuser',
          // 'password' => 'authpass',
        // ),
      ),
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
    'install' => false, // DEFAULT = FALSE Will check for existence of collections and documents and build it if none
    'host' => 'http://localhost:8080/exist/services/Query?wsdl',
    'user' => 'myuser', // default / anonymouse credentials
    'password' => 'mypass', // default / anonymouse credentials
    'collection' => '/mycollection',
    'namespace' => 'http://www.example.com',
    'default' => array(
      'group' => 'dba',
      'mode' => '0770', // octal value
    ),
    'debug' => array(
      'run' => true, // DEFAULT = true
      'queries' => array(
        'show' => false, // DEFAULT = false
        'statut' => 'db/notice',
      ),
      'results' => array(
        'show' => false, // DEFAULT = false
        'statut' => 'db/notice',
      ),
    ),
  ),
  
  'directories' => array(
    'root' => array(
      'path' => 'protected',
      'rights' => array('owner' => 'root', 'group' => '0', 'mode' => '700', 'user-mode' => null),
    ),
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
    'print' => array(
      'all' => false, // DEFAULT = false
      'hidden' => true, // Messages sent before Messages handler's creation. Only available in DEBUG mode : DEFAULT = false
    ),
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
      'enable' => false, // DEFAULT = false
    ),
    'rights' => array(
      'enable' => true, // DEFAULT = true
    ),
  ),
  
  'actions' => array(
    'redirect' => array(
      'enable' => true, // Redirect on action's error to 404 : DEFAULT = true
    ),
    'stats' => array(
      'enable' => true, // Display actions infos (memory leak !) : DEFAULT = true
    ),
  ),
);



