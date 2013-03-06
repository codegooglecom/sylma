<?php

namespace sylma\schema\xsd\component;
use sylma\core, sylma\dom, sylma\schema\parser, sylma\parser\reflector;

class Element extends parser\component\Element {

  public function parseRoot(dom\element $el) {

    $this->setName($el->readx('@name'));

    if ($sType = $el->readx('@type', array(), false)) {

      $this->setType($this->getParser()->getType($sType, $el));
    }
    else {

      $this->setType($this->parseComponent($el->getFirst()));
    }
  }

}

