<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template\parser;

class Variable extends Child implements common\arrayable, parser\component {

  protected $sName;
  protected $var;

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

    $this->getTemplate()->setVariable($this);

    $aContent = $this->parseComponentRoot($this->getNode());

    $this->loadVar($aContent);
  }

  protected function loadVar($mContent) {

    if ($mContent instanceof common\_var) {

      $this->setVar($mContent);
    }
    else {

      $this->setVar($this->getWindow()->createVar($this->getWindow()->toString($mContent)));
    }
  }

  protected function setVar(common\_var $var) {

    $this->var = $var;
  }

  public function getVar() {

    if (!$this->var) {

      $this->launchException('Variable not ready');
    }

    return $this->var;
  }

  public function asArray() {

    $this->build();

    return array($this->getVar()->getInsert());
  }
}

