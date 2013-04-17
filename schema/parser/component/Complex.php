<?php

namespace sylma\schema\parser\component;
use sylma\core, sylma\schema\parser, sylma\parser\reflector;

class Complex extends Basic implements parser\type, parser\type\complex {

  protected $aParticles = array();
  protected $sName = '';

  public function isComplex() {

    return true;
  }

  public function isSimple() {

    return false;
  }

  protected function setName($sName) {

    $this->sName = $sName;
  }

  public function getName() {

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

  public function getNamespace($sPrefix = '') {

    return parent::getNamespace($sPrefix);
  }
}

