<?php

namespace sylma\dom\basic;
use sylma\core, sylma\dom;

class Instruction extends \DOMProcessingInstruction implements dom\node {

  public function __toString() {

  }

  public function getDocument() {

  }

  public function getParent() {

    return $this->parentNode;
  }

  public function getType() {

    return self::INSTRUCTION;
  }

  public function compare(dom\node $element) {

    if ($element->getType() == $this->getType() && $element->asString() == $this->asString()) return self::COMPARE_SUCCESS;

    return self::COMPARE_BAD_ELEMENT;
  }

  public function remove() {

    return $this->getParent()->removeChild($this);
  }
  
  public function asString() {

    return '<?' . $this->target . ' ' . $this->data . ' ?>';
  }

  public function asToken() {

  }
}

