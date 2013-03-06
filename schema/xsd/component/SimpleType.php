<?php

namespace sylma\schema\xsd\component;
use sylma\core, sylma\dom, sylma\schema\parser, sylma\parser\reflector;

class SimpleType extends parser\component\Simple {

  public function parseRoot(dom\element $el) {

    $el = $this->setNode($el);

    $this->setName($el->readx('@name'));
    
  }
}
