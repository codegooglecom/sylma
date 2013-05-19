<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\dom, sylma\schema;

class Table extends schema\xsd\component\Element {

  public function parseRoot(dom\element $el) {

    $this->setNode($el, false);
    $this->setName($el->readx('@name'));

    $parser = $this->getParser();
    $type = $this->loadSimpleComponent('component/complexType', $parser);
    $particle = $this->loadComponent('component/particle', $el, $parser);

    $type->addParticle($particle);
    $this->setType($type);
  }

  public function loadNamespace($sNamespace = '') {

    parent::loadNamespace($sNamespace);
    if ($this->getType(false)) $this->getType()->loadElements($this->getNamespace());
  }

  public function asString() {

    return "`" . $this->getName() . "`";
  }
}

