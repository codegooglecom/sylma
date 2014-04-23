<?php

namespace sylma\crud\_list;
use sylma\core;

class Dummy extends core\module\Domed {

  protected $sToken;
  protected $bReset = false;

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

  protected function loadDatas() {

    if (!$this->bReset) {

      $current = $this->getSettings();
      $this->setSettings($this->getSession(), false);
      $this->setSettings($current);
    }
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
//dsp($sPath, parent::read($sPath, $bDebug), $bDebug);
    return parent::read($sPath, $bDebug);
  }

  protected function reset() {

    $this->bReset = true;

    $this->setSettings(array(), false);
    $this->save();
  }

  public function save() {

    $this->setSession($this->getSettings()->asArray());
  }
}
