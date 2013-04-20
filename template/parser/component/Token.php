<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template\parser;

class Token extends Child implements common\arrayable, parser\component {

  public function parseRoot(dom\element $el) {

    $this->allowForeign(true);
    $this->setNode($el);
  }

  protected function parseChildrenText(dom\text $node, array &$aResult) {

    $aResult[] = $node->getValue();
  }

  public function asArray() {

    $el = $this->getNode();

    $content = $this->parseChildren($el->getChildren());

    if (is_array($content) && count($content) === 1) {

      $content = current($content);
    }

    $element = $this->getTemplate()->getElement();
    $element->addToken($this->readx('@name'), $content);

    return array();
  }
}

