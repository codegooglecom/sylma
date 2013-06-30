<?php

namespace sylma\core\module;
use sylma\core;

abstract class Sessioned extends Argumented {

  const CLASS_PREFIX = 'sylma-class-';

  private function getSessionKey() {

    return self::CLASS_PREFIX . get_class($this);
  }

  protected function setSession($mValue) {

    $_SESSION[$this->getSessionKey()] = $mValue;
  }

  protected function getSession() {

    return isset($_SESSION[$this->getSessionKey()]) ? $_SESSION[$this->getSessionKey()] : null;
  }
}


