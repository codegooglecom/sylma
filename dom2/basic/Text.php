<?php

namespace sylma\dom\basic;
use \sylma\dom;

require_once(dirname(__dir__) . '/text.php');

class Text extends \DOMText implements dom\text {

  public function getDocument() {

    return $this->ownerDocument;
  }

  public function getParent() {

    return $this->parentNode;
  }

  public function getType() {

    return $this->nodeType;
  }

  public function getValue() {

    return $this->nodeValue;
  }

  public function asString($bFormat = false) {

    return $this->getValue();
  }

  public function asToken() {

    return '@text in ' . $this->getParent()->asToken();
  }

  public function __toString() {

    return $this->asString();
  }
}
