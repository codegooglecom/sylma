<?php

/**
 * $sylma is the main global variable to set global config values in a structured array trees
 * This array can be override with @function array_merge_keys() defined below
 * An array is necessary for process appending before xml lib is usable (TODO details)
 * In a second time, $sylma may be merged into root.xml settings for use with the xml lib
 */
$sylma = array(
  
  'users' => array(
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
    'enable' => null,
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
  ),
  
  'directories' => array(
    'root' => array(
      'rights' => array('owner' => 'root', 'group' => '0', 'mode' => '700', 'user-mode' => null),
    ),
  ),
  
);

/**
 * Will merge array recurively, but instead of replaced by array, similar keys are erased
 * @param array $array1 The source array, for wich values could be replaced
 * @param array $array2 The second array that will override first argument array
 * @author andyidol at gmail dot com - http://www.php.net/manual/en/function.array-merge-recursive.php#102379
 * @author Rodolphe Gerber
 */
function array_merge_keys(array $array1, array $array2) {
  
  foreach($array2 as $key => $val) {
    
    if(array_key_exists($key, $array1) && is_array($val)) {
      
      $array1[$key] = array_merge_keys($array1[$key], $array2[$key]);
    }
    else {
      
      $array1[$key] = $val;
    }
  }

  return $array1;
}



