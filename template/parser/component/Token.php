<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template\parser;

class Token extends Child implements parser\component {

  protected $sName;
  protected $element;

  public function parseRoot(dom\element $el) {

    $this->allowText(true);
    $this->allowForeign(true);
    $this->setNode($el);

    $this->loadName();
  }

  protected function loadName() {

    $this->sName = $this->readx('@name');
  }

  public function getName() {

    return $this->sName;
  }

  public function setElement(Element $el) {

    $this->element = $el;
  }

  protected function getElement() {

    if (!$this->element) {

      $this->launchException('No element defined');
    }

    return $this->element;
  }

  public function asValue() {

    $el = $this->getNode();

    $content = $this->parseChildren($el->getChildren());

    if (is_array($content) && count($content) === 1) {

      $content = current($content);
    }

    return $content;
  }

  protected function parseText(dom\text $node, $bTrim = true) {

    return $bTrim ? trim($node->getValue()) : $node->getValue();
  }

  public function getCall() {

    $sName = $this->getName();

    $this->log("Token ({$sName})");

    $element = $this->getElement();
    $result = $element->addToken($sName, $this->getWindow()->toString($this->asValue()));

    return $result;
  }
}
