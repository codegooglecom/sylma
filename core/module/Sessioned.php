<?php

namespace sylma\core\module;
use sylma\core;

abstract class Sessioned extends Argumented {

  const CLASS_PREFIX = 'sylma-class-';

  private function getSessionKey() {

    return self::CLASS_PREFIX . get_class($this);
  }

  protected function setSession($sValue) {

    $_SESSION[$this->getSessionKey()] = $sValue;
  }

  protected function getSession() {

    return $_SESSION[$this->getSessionKey()];
  }
}


