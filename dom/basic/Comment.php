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

    return $this->ownerDocument;
  }

  public function getValue() {

    return $this->nodeValue;
  }

  public function remove() {

    try {

      $result = $this->getParent()->removeChild($this);
    }
    catch (\DOMException $e) {

      \Sylma::throwException($e->getMessage());
    }

    return $result;
  }

  public function asString($iMode = 0) {

    return '<!--'. $this->getValue() .'-->';
  }

  public function asToken() {

    return $this->getParent()->asToken() . ' @comment ' . htmlentities($this->getValue());
  }

  public function __toString() {

    return $this->getValue();
  }
}

