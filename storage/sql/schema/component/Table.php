<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\schema;

class Table extends schema\xsd\component\Element {

  public function parseRoot(\sylma\dom\element $el) {

    $this->setNode($el, false);
    $this->setName($el->readx('@name'));
    //$this->loadNamespace();

    $parser = $this->getParser();
    $type = $this->loadSimpleComponent('component/complexType', $parser);
    $particle = $this->loadComponent('component/particle', $el, $parser);

    $type->addParticle($particle);


    $this->setType($type);
  }

  public function asString() {

    return "`" . $this->getName() . "`";
  }
}

