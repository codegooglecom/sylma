<?php

namespace sylma\core\argument;
use \sylma\core;

abstract class Iterator extends Normalizer implements \ArrayAccess {

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

  public function offsetSet($offset, $value) {

    if (is_null($offset)) {

      $this->aArray[] = $value;

    } else {

      $this->aArray[$offset] = $value;
    }
  }

  public function offsetExists($offset) {

    return isset($this->aArray[$offset]);
  }

  public function offsetUnset($offset) {

    unset($this->aArray[$offset]);
  }

  public function offsetGet($offset) {

    return isset($this->aArray[$offset]) ? $this->aArray[$offset] : null;
  }

}
