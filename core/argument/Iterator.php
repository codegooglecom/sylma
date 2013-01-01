<?php

namespace sylma\core\argument;
use \sylma\core;

class Iterator extends Basic implements core\argument {

  public function rewind() {

    reset($this->aArray);
  }

  public function current() {

    $result = null;

    $sKey = $this->key();

    if (!is_null($sKey)) {

      $result = $this->get($sKey, false);

      if (!$result) {

        $result = $this->read($sKey);
        //$this->next();
        //if ($this->valid()) $result = $this->current();
      }
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

    $sKey = key($this->aArray);

    return $sKey !== NULL && $sKey !== FALSE;
  }
}
