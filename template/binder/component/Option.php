<?php

namespace sylma\template\binder\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Option extends Basic implements common\arrayable {

  protected $sName;
  protected $value;

  public function parseRoot(dom\element $el) {

    $this->allowForeign(true);
    $this->allowText(true);
    $this->setNode($el);
  }

  protected function setName($sName) {

    $this->sName = $sName;
  }

  protected function setValue($val) {

    $this->value = $val;
  }

  protected function getName() {

    return $this->sName;
  }

  protected function getValue() {

    return $this->value;
  }

  protected function build() {

    $el = $this->getNode();

    $content = $this->parseChildren($el->getChildren());

    if (is_array($content) && count($content) === 1) {

      $content = current($content);
    }

    $this->setName($el->readx('@name'));
    $this->setValue($content);
  }

  public function asArray() {

    $this->build();

    $var = $this->getParser()->getObjects();
    $window = $this->getPHPWindow();
    $content = current($window->parseArrayables(array($window->createCast($this->getValue()))));
    
    return array($var->call('addOption', array($this->getName(), $content)));

    return array();
  }
}

