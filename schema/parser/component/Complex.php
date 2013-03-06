<?php

namespace sylma\schema\parser\component;
use sylma\core, sylma\schema\parser, sylma\parser\reflector;

class Complex extends Basic implements parser\type {

  protected $aParticles = array();
  protected $sName = '';

  public function isComplex() {

    return true;
  }

  protected function setName($sName) {

    $this->sName = $sName;
  }

  protected function getName() {

    return $this->sName;
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
  public function getElement($sName) {

    $result = null;

    foreach ($this->getParticles() as $particle) {

      if ($result = $particle->getElement($sName)) break;
    }

    return $result;
  }

  public function getNamespace($sPrefix = '') {

    return parent::getNamespace($sPrefix);
  }
}

