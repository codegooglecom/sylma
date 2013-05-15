<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template\parser;

class Token extends Child implements common\arrayable, parser\component {

  protected $sName;

  public function parseRoot(dom\element $el) {

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

  protected function parseChildrenText(dom\text $node, array &$aResult) {

    $aResult[] = $node->getValue();
  }

  public function asValue() {

    $el = $this->getNode();

    $content = $this->parseChildren($el->getChildren());

    if (is_array($content) && count($content) === 1) {

      $content = current($content);
    }

    return $content;
  }

  public function asArray() {

    $sName = $this->getName();

    $this->startLog("Token ({$sName})");

    $element = $this->getRoot()->getCurrentElement();
    $element->addToken($sName, $this->asValue());

    $this->stopLog();

    return array();
  }
}

