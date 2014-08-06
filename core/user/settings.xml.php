<?php
    return new \sylma\core\argument\parser\Cached(array(
'cookies' => array(
  'name' => 'sylma-user',
  'secret-key' => 'This value is not really secret',
  'lifetime' => array(
    'short' => '28800',
    'normal' => '403200'),
  'remember' => array(
    'name' => 'sylma-remember',
    'lifetime' => '4032000')),
'session' => array(
  'name' => 'sylma-user',
  'lifetime' => '28800'),
'login' => array(
  'delay' => '1'),
'classes' => array(
  'user' => array(
    'file' => '\sylma\core\user\Basic.php',
    'name' => '\sylma\core\user\Basic'),
  'cookie' => array(
    'file' => '\sylma\core\user\Cookie.php',
    'name' => '\sylma\core\user\Cookie'),
  'redirect' => array(
    'file' => '\sylma\core\Redirect.php',
    'name' => '\sylma\core\Redirect'))));
  