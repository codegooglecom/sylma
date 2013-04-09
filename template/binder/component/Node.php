<?php

namespace sylma\template\binder\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\template;

class Node extends reflector\component\Foreigner implements common\arrayable {

  protected $sName;
  protected $value;
  protected $element;

  public function parseRoot(dom\element $el) {

    $this->setNode($el, false);

    $this->allowForeign(true);
    $this->allowUnknown(true);
  }

  protected function getElement() {

    return $this->element;
  }

  protected function setElement(template\element $element) {

    $this->element = $element;
  }

  public function build(template\element $newElement) {

    $this->setElement($newElement);

    $sName = $this->readx('@js:node');
    $sClass = uniqid('sylma-');

    $this->getObject()->setProperty("nodes.$sName", $sClass);

    $el = $this->getNode();

    //$el->addToken('class', $sClass);
    //$el->setAttribute('class', $sClass);
    $newElement->parseRoot($this->cleanAttributes($el));
    $newElement->addToken('class', $sClass);
  }

  protected function getObject() {

    return $this->getParser()->getObject();
  }

  public function asArray() {

    return $this->getElement()->asArray();
  }
}

