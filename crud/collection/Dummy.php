<?php

namespace sylma\crud\collection;
use sylma\core;

class Dummy extends core\module\Domed {

  protected $sToken;
  protected $bReset = false;
  protected $aDefaults = array();

  public function __construct(core\argument $args, core\argument $post) {

    $this->setSettings($args);
    $this->setSettings($post);

    if ($this->read('sylma-reset', false)) {

      $this->reset();
    }
  }

  protected function getSessionKey() {

    return 'sylma-form-' . $this->getToken();
  }

  public function setToken($sValue) {

    $this->sToken = $sValue;
    $this->loadDatas();
  }

  public function setDefaults(array $aDefaults) {

    $this->aDefaults = $aDefaults;
  }

  public function getDefaults() {

    return $this->aDefaults;
  }

  protected function loadDefaults() {

    $this->setSettings($this->getDefaults());
  }

  protected function loadDatas() {

    $current = $this->getSettings();

    if (!$this->bReset) {

      $this->setSettings(array(), false);
      $this->loadDefaults();

      $this->setSettings($this->getSession());
    }

    $this->setSettings($current);
  }

  protected function getToken() {

    return $this->sToken;
  }

  public function setDefault($sKey, $sValue) {

    if (!$this->read($sKey, false)) {

      $this->set($sKey, $sValue);
    }
  }

  public function read($sPath, $bDebug = true) {

    return parent::read($sPath, $bDebug);
  }

  public function query($sPath = '', $bDebug = true) {

    return parent::query($sPath, $bDebug);
  }

  protected function reset() {

    $this->bReset = true;

    $this->setSettings(array(), false);
    $this->loadDefaults();

    $this->save();
  }

  public function save() {

    $this->setSession($this->getSettings()->asArray());
  }
}
