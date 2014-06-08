<?php

namespace sylma\schema\parser\component;
use sylma\core, sylma\schema\parser, sylma\parser\reflector;

class Complex extends Type implements parser\type, parser\type\complex {

  protected $aParticles = array();

  public function isComplex() {

    return true;
  }

  public function isSimple() {

    return false;
  }

  protected function addParticle(parser\particle $particle) {

    $this->aParticles[] = $particle;
  }

  public function getParticles() {

    return $this->aParticles;
  }

  /**
   *
   * @param parser\element
   */
  public function getElement($sName, $sNamespace) {

    $result = null;

    foreach ($this->getParticles() as $particle) {

      if ($result = $particle->getElement($sName, $sNamespace)) break;
    }

    return $result;
  }

  public function getElements() {

    $aResult = array();

    foreach ($this->getParticles() as $particle) {

      $aResult = array_merge($aResult, $particle->getElements());
    }

    return $aResult;
  }

  public function __clone() {

    $aParticles = array();

    foreach ($this->getParticles() as $particle) {

      $aParticles[] = clone $particle;
    }

    $this->aParticles = $aParticles;
  }
}

