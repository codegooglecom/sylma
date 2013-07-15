<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\schema;

class ComplexType extends schema\xsd\component\ComplexType {

  const NS = 'http://2013.sylma.org/storage/sql';
  const NAME = 'table';

  public function loadElements($sNamespace) {

    foreach ($this->getParticles() as $particle) {

      $particle->loadElements($sNamespace);
    }
  }

  public function loadIdentity() {

    $this->setName(self::NAME);
    $this->setNamespace(self::NS);
  }

  public function addParticle(schema\parser\particle $particle) {

    return parent::addParticle($particle);
  }
}

