<?php

namespace sylma\dom\argument;
use sylma\core, sylma\dom;

class Iterator extends Basic implements core\argument {

  protected $collection;

  protected function getChildren() {

    if (!$this->collection) {

      $this->collection = $this->getDocument()->getChildren();
    }

    return $this->collection;
  }

  public function rewind() {

    $this->getChildren()->rewind();
  }

  public function current() {

    $dom = $this->getControler();
    return $result = new Iterator($dom->create('handler', array($this->getChildren()->current())), $this->getNS());
  }

  public function key() {

    $node = $this->getChildren()->current();
    return $node->getName();
  }

  public function next() {

    $this->getChildren()->next();
  }

  public function valid() {

    return $this->getChildren()->valid();
  }

  public function asJSON() {

    $this->throwException('Not implemented');
  }
}
