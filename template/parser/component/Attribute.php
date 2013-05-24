<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template\parser;

class Attribute extends Child implements common\arrayable, parser\component {

  protected $sName;
  protected $var;

  public function parseRoot(dom\element $el) {

    //$this->allowForeign(true);
    $this->allowText(true);
    $this->setNode($el);

    $this->loadName();
  }

  protected function loadName() {

    $this->sName = $this->readx('@name');
  }

  public function getName() {

    return $this->sName;
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

    $element = $this->getRoot()->getCurrentElement();
    $element->setAttribute($this->getName(), $this->asValue());

    return array($this->getVar());
  }
}

