<?php

namespace sylma\core\module;
use sylma\core;

require_once('Argumented.php');

abstract class Sessioned extends Argumented {

  const CLASS_PREFIX = 'sylma-class-';

  private function getKey() {

    return self::CLASS_PREFIX . get_class($this);
  }

  protected function setSession($sValue) {

    $_SESSION[$this->getKey()] = $sValue;
  }

  protected function getSession() {
    
    return $_SESSION[$this->getKey()];
  }
}


