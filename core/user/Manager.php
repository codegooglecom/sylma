<?php

namespace sylma\core\user;
use sylma\core;

class Manager extends core\module\Filed {

  protected $user;

  public function __construct() {

    $this->setDirectory(__FILE__);

    $this->setArguments(include('settings.xml.php'));
    $this->getArguments()->merge(\Sylma::get('users'));

    $user = $this->createUser();
    $user->load();

    $this->setUser($user);
  }

  public function createUser() {

    return $this->create('user', array($this));
  }

  public function getUser() {

    return $this->user;
  }

  protected function setUser(core\user $user) {

    $this->user = $user;
  }

  public function getDocument($sPath = '', $bDebug = true) {

    return parent::getDocument($sPath, $bDebug);
  }
}
