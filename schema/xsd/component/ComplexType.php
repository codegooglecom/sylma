<?php

namespace sylma\schema\xsd\component;
use sylma\core, sylma\dom, sylma\schema\parser, sylma\parser\reflector;

class ComplexType extends parser\component\Complex {

  public function parseRoot(dom\element $el) {

    $this->setName($el->readx('@name', array(), false));

    if ($content = $el->getx('self:complexContent', array(), false)) {


    }
    else {

      $this->loadParticles($el);
    }
  }

  protected function loadParticles(dom\element $el) {

    foreach ($el->getChildren() as $child) {

      $particle = $this->parseComponent($child);
      $this->addParticle($particle);
    }
  }

  public function asArray() {

    return array(

    );
  }
}


