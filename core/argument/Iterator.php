<?php

namespace sylma\core\argument;
use \sylma\core;

require_once('Basic.php');

class Iterator extends Basic {

  public function rewind() {

    reset($this->aArray);
  }

  public function current() {

    $result = null;

    $sKey = key($this->aArray);

    if (!$result = $this->get($sKey, false)) {

      $result = $this->read($sKey);
      //$this->next();
      //if ($this->valid()) $result = $this->current();
    }

    return $result;
  }

  public function key() {

    return key($this->aArray);
  }

  public function next() {

    next($this->aArray);
  }

  public function valid() {

    return current($this->aArray) !== false;
  }
}
