<?php

namespace sylma\dom\basic;
use \sylma\dom, \sylma\core;

require_once(dirname(__dir__) . '/attribute.php');

class Attribute extends \DOMAttr implements dom\attribute {

  public function __toString() {

    return $this->nodeValue;
  }

  public function getDocument() {

    return $this->ownerDocument;
  }

  public function getType() {

    return $this->nodeType;
  }

  public function getParent() {

    return $this->ownerElement;
  }

  public function getPrefix() {

    return $this->prefix;
  }

  public function getName($bFull = false) {

    if ($bFull && $this->getPrefix()) return $this->getPrefix().':'.$this->name;
    else return $this->name;
  }

  public function getNamespace() {

    return $this->namespaceURI;
  }

  public function getValue() {

    return $this->value;
  }

  public function asString($bFormat = false) {

    return $this->getName(true).'="'.dom\xmlize($this->value).'"';
  }

  public function asToken() {

    return $this->getParent()->asToken() . ' @attribute ' . $this->getName(true) . ' = ' . $this->getValue();
  }
}

