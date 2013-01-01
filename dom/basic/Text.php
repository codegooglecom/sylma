<?php

namespace sylma\dom\basic;
use \sylma\dom;

require_once(dirname(__dir__) . '/text.php');

class Text extends \DOMText implements dom\text {

  public function __construct($sValue) {

    if (Sylma::read('dom/encoding/check') && !mb_check_encoding($sValue, 'UTF-8')) {

      //$sValue = utf8_encode($sContent); // TODO , result not always in utf-8
      $this->getParent()->throwException('Bad encoding');
    }

    parent::__construct($sValue);
  }
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

  public function asString($iMode = 0) {

    return $this->getValue();
  }

  public function compare(dom\node $element) {

    if ($element->getType() == self::TEXT && $element->getValue() == $this->getValue()) return self::COMPARE_SUCCESS;

    return self::COMPARE_BAD_ELEMENT;
  }

  public function asToken() {

    $parent = $this->getParent();

    return '@text in ' . ($parent ? $this->getParent()->asToken() : '[no parent]');
  }

  public function __toString() {

    return $this->asString();
  }
}
