<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template\parser;

class Variable extends Child implements common\arrayable, parser\component {

  protected $sName;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->loadName();

    $this->allowForeign(true);
    $this->allowText(true);
  }

  protected function loadName() {

    $this->sName = $this->readx('@name');
  }

  public function getName() {

    return $this->sName;
  }

  protected function build() {

    $window = $this->getWindow();
    $this->getTemplate()->setVariable($this);

    $aContent = $this->parseComponentRoot($this->getNode());

    $this->var = $window->addVar($window->toString($aContent));
  }

  public function getVar() {

    return $this->var;
  }

  public function asArray() {

    $this->build();

    return array();
  }
}

