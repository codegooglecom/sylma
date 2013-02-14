<?php

namespace sylma\parser\security;
use sylma\core, sylma\parser\reflector, sylma\dom, sylma\parser\languages\php;

abstract class Main extends reflector\handler\Elemented {

  protected $parent;

  protected function reflectTest(array $aRights) {

    $window = $this->getWindow();

    $user = $window->addControler('user');
    $call = $window->createCall($user, 'getMode', 'php-boolean', array($aRights['user'], $aRights['group'], $aRights['mode']));

    $mode = $window->argToInstance(\Sylma::MODE_EXECUTE);
    $test = $window->create('test', array($window, $call, $mode, '&'));

    return $test;
  }

  protected function reflectRights($result, array $aRights) {

    $window = $this->getWindow();

    return $window->createCondition($this->reflectTest($aRights), $result);
  }
}