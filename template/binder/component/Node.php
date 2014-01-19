<?php

namespace sylma\template\binder\component;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template;

class Node extends Basic implements common\arrayable {

  protected $sName;
  protected $sClass;
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

    $sClass = uniqid('sylma-');
    $this->setClass($sClass);

    $sName = $this->readx('@js:node');
    $this->setName($sName);

    $el = $this->getNode();

    $newElement->parseRoot($this->cleanAttributes($el));
    $newElement->addToken('class', $sClass);

    if ($obj = $this->getObject(false)) {

      $this->isBuilt(true);
      $this->addProperty($this->getObject());
    }
  }

  protected function addProperty(template\binder\_class $class) {

    $sClass = $this->getClass();
    $sName = $this->getName();

    $class->setProperty("nodes.$sName", $sClass);
  }

  protected function setName($sName) {

    $this->sName = $sName;
  }

  protected function getName() {

    if (!$this->sName) {

      $this->launchException('No name defined');
    }

    return $this->sName;
  }

  protected function setClass($sName) {

    $this->sClass = $sName;
  }

  protected function getClass() {

    return $this->sClass;
  }

  public function asArray() {

    if (!$this->isBuilt()) {

      $this->addProperty($this->extractClass($this->getObject()));
    }

    return $this->getElement()->asArray();
  }
}

