<?php

namespace sylma\dom\basic;
use \sylma\dom, \sylma\core;

class Comment extends \DOMComment implements dom\node {

  public function getDocument() {

    return $this->ownerDocument;
  }

  public function getType() {

    return $this->nodeType;
  }

  public function getParent() {

    return $this->ownerElement;
  }

  public function getValue() {

    return $this->nodeValue;
  }

  public function remove() {

    return $this->getParent()->removeChild($this);
  }

  public function asString($iMode = 0) {

    return '<!--'. $this->getValue() .'-->';
  }

  public function asToken() {

    return $this->getParent()->asToken() . ' @comment ' . $this->getValue();
  }

  public function __toString() {

    return $this->getValue();
  }
}

