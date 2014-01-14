<?php

namespace sylma\dom\basic;
use sylma\dom;

class CData extends \DOMCdataSection implements dom\node {

  public function setValue($mValue) {

    $this->data = (string) $mValue;
  }

  public function getValue() {

    return $this->data;
  }

  public function remove() {

    return $this->parentNode->removeChild($this);
  }

  public function getType() {

    return $this->nodeType;
  }

  public function asToken() {

    $parent = $this->getParent();
    return '@cdata in ' . ($parent ? $this->getParent()->asToken() : '[no parent]');
  }

  public function getDocument() {

    return $this->ownerDocument;
  }

  public function getParent() {

    return $this->parentNode;
  }

  public function asString() {

    //return "<![CDATA[\n".$this->data."]]>\n";
    return $this->data;
  }

  public function compare(dom\node $element) {

    if ($element->getType() == self::CDATA && $element->getValue() == $this->getValue()) return self::COMPARE_SUCCESS;

    return self::COMPARE_BAD_ELEMENT;
  }

  public function __toString() {

    return $this->asString();
  }
}

