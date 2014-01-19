<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template\parser;

class Constant extends Child implements common\arrayable, parser\component {

  protected $sName;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->loadName();

    $this->build();
  }

  protected function loadName() {

    $this->sName = $this->readx('@name');
  }

  public function getName() {

    return $this->sName;
  }

  protected function build() {

    $this->log("Constant '{$this->getName()}'", array('content' => $this->readx()));

    $this->getParser()->setConstant($this->getName(), $this->readx());
  }

  public function asArray() {

    return array();
  }
}

