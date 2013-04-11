<?php

namespace sylma\dom\basic;
use \sylma\dom, \sylma\core;

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

  public function setValue($mValue) {

    $this->value = (string) htmlentities($mValue);
  }

  public function remove() {

    $this->getParent()->removeAttributeNode($this);
  }

  public function asString($iMode = 0) {

    \Sylma::load('/dom/functions.php');

    return $this->getName(true).'="'.dom\xmlize($this->value).'"';
  }

  public function asToken() {

    return $this->getParent()->asToken() . ' @attribute ' . $this->getName(true) . ' = ' . $this->getValue();
  }
}

