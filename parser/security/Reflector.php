<?php

namespace sylma\parser\security;
use sylma\core, sylma\parser, sylma\dom, sylma\parser\languages\php;

require_once('core/module/Filed.php');

abstract class Reflector extends core\module\Filed {

  const NS = 'http://www.sylma.org/parser/security';

  protected $parent;

  public function __construct() {

    $this->setNamespace(self::NS);
  }

  public function setParent(parser\compiler\elemented $parent) {

    $this->parent = $parent;
  }

  protected function getParent() {

    return $this->parent;
  }

  protected function reflectTest(array $aRights) {

    $window = $this->getParent()->getWindow();

    $user = $window->addControler('user');
    $call = $window->createCall($user, 'getMode', 'php-boolean', array($aRights['user'], $aRights['group'], $aRights['mode']));

    $mode = $window->argToInstance(\Sylma::MODE_EXECUTE);
    $test = $window->create('test', array($window, $call, $mode, '&'));

    return $test;
  }

  protected function reflectRights($result, array $aRights) {

    $window = $this->getParent()->getWindow();

    return $window->createCondition($this->reflectTest($aRights), $result);
  }
}