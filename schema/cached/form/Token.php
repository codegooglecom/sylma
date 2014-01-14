<?php

namespace sylma\schema\cached\form;
use sylma\core;

class Token extends core\module\Sessioned {

  const TOKEN_PREFIX = 'sylma-token-';

  protected $sPath;

  public function __construct($sPath = '') {

    $this->setSettings(\Sylma::get('schema/token'));
    if ($sPath) $this->setPath($sPath);
  }

  public function reset() {

    $this->setSession(array());
  }

  public function savePath($sPath) {

    $aSession = $this->getSession();

    $aContent = array(
      self::TOKEN_PREFIX . $sPath => array('time' => microtime(true)),
    );

    if (!$aSession || !is_array($aSession)) {

      $aResult = $aContent;
    }
    else {

      $aResult = array_merge($aSession, $aContent);
    }

    $this->setSession($aResult);
  }

  protected function setPath($sPath) {

    $this->sPath = $sPath;
  }

  protected function getPath() {

    return $this->sPath;
  }

  public function isValid() {

    $sPath = self::TOKEN_PREFIX . $this->getPath();

    $aSession = $this->getSession();

    if (!isset($aSession[$sPath])) {

      $this->launchException('Bad token', get_defined_vars());
    }

    if ((microtime(true) - $aSession[$sPath]['time']) > $this->read('duration')) {

      $this->launchException('Token expired');
    }
  }
}

