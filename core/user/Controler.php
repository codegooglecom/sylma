<?php

namespace sylma\core\user;
use sylma\core;

require_once('core/module/Domed.php');

class Controler extends core\module\Domed {

  protected $user;

  const SETTINGS_FILE = 'settings.yml';

  public function __construct() {

    $this->setDirectory(__FILE__);

    $this->setArguments(self::SETTINGS_FILE);
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
