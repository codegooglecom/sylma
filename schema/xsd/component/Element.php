<?php

namespace sylma\schema\xsd\component;
use sylma\core, sylma\dom, sylma\schema\parser, sylma\parser\reflector;

class Element extends parser\component\Element {

  public function parseRoot(dom\element $el) {

    $this->setNode($el, false);

    $this->loadName();
    //$this->loadNamespace();

    if ($sType = $this->readx('@type')) {

      list($sNamespace, $sName) = $this->parseName($sType);

      $this->setType($this->getParser()->getType($sName, $sNamespace));
    }
    else {

      $this->setType($this->parseComponent($el->getFirst()));
    }
  }

  protected function loadName() {

    $this->setName($this->readx('@name'));
  }

  public function loadNamespace($sNamespace = '') {

    if (!$sNamespace) $sNamespace = $this->getParser()->getTargetNamespace();

    $this->setNamespace($sNamespace, 'element');
  }
}

