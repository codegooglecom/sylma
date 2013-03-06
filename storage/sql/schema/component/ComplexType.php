<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\schema;

class ComplexType extends schema\xsd\component\ComplexType {

  public function addParticle(schema\parser\particle $particle) {

    return parent::addParticle($particle);
  }
}

