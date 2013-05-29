<?php

namespace sylma\schema\parser\component;
use sylma\core, sylma\dom, sylma\schema, sylma\parser\reflector, sylma\parser\languages\common;

class Element extends Basic implements schema\parser\element, core\tokenable {

  protected $type;
  protected $parent;
  protected $particle;

  protected $iPosition = 0;

  protected $iMinOccurs;
  protected $iMaxOccurs;

  protected $bOptional = false;

  protected $reflector;

  public function setParent(schema\parser\element $parent) {

    $this->parent = $parent;
  }

  /**
   * @param boolean $bDebug
   * @return \sylma\schema\parser\element
   */
  public function getParent($bDebug = true) {

    if (!$this->parent && $bDebug) {

      $this->throwException('No parent');
    }

    return $this->parent;
  }

  public function parseName($sName) {

    return $this->getParser()->parseName($sName, $this, $this->getNode(false));
  }

  /**
   * @TODO Bad mix with module\Namespaced
   */
  public function getNamespace($sPrefix = 'element') {

    return parent::getNamespace($sPrefix);
  }

  public function setPosition($iPosition) {

    $this->iPosition = $iPosition;
  }

  protected function getPosition() {

    return $this->iPosition;
  }

  public function setParticle(schema\parser\particle $particle) {

    $this->particle = $particle;
  }

  protected function getParticle() {

    if (!$this->particle) {

      $this->launchException('No particle defined');
    }

    return $this->particle;
  }

  protected function getPrevious() {

    $result = null;

    if ($this->getPosition()) {

      $result = $this->getParticle()->getElementFromIndex($this->getPosition() - 1);
    }

    return $result;
  }

  protected function getNext() {

    return $this->getParent()->getElementFromIndex($this->getPosition() + 1);
  }

  /**
   * @param bool $bDebug
   * @return \schema\parser\type
   */
  public function getType($bDebug = true) {

    if (!$this->type) {

      if ($bDebug) $this->throwException('No type defined');
    }

    return $this->type;
  }

  public function setType(schema\parser\type $type) {

    $this->type = $type;
  }

  public function getElement($sName, $sNamespace = null, $bDebug = true) {

    if (is_null($sNamespace)) {

      $sNamespace = $this->getNamespace();
    }

    if (!$this->isComplex()) {

      $this->launchException("Cannot get sub element of simple typed element $sNamespace:$sName", get_defined_vars());
    }

    if ($result = $this->getType()->getElement($sName, $sNamespace)) {

      $this->loadChild($result);
    }
    else {

      if ($bDebug) $this->launchException("Cannot find element $sNamespace:$sName", get_defined_vars());
      $result = null;
    }

    return $result;
  }

  public function getElements() {

    if (!$this->isComplex()) {

      $this->launchException('Cannot get sub elements of complex type');
    }

    $aChildren = $this->getType()->getElements();

    foreach ($aChildren as $child) {

      $this->loadChild($child);
    }

    return $aChildren;
  }

  protected function loadChild(schema\parser\element $child) {

    $child->setParent($this);
    $child->loadNamespace($this->getNamespace());
  }

  public function isComplex() {

    return $this->getType()->isComplex();
  }

  protected function isRequired() {

    return !$this->isOptional();
  }

  protected function isOptional($bValue = null) {

    if (is_bool($bValue)) $this->bOptional = $bValue;

    return $this->bOptional;
  }

  protected function setOccurs($iMin, $iMax) {

    $this->iMinOccurs = $iMin;
    $this->iMaxOccurs = $iMax;
  }

  protected function getMinOccurs() {

    return $this->iMinOccurs;
  }

  protected function getMaxOccurs($bBool) {

    return $bBool ? $this->iMaxOccurs == 'n' || $this->iMaxOccurs > 1 : $this->iMaxOccurs;
  }

  protected function getReflectorStatic() {

    if (is_null($this->reflector)) {

      if (!$result = $this->parseReflector(array(), true)) {

        $result = $this->getType()->getReflectorStatic();
      }

      $this->reflector = $result;
    }

    return $this->reflector;
  }

  public function buildReflector(array $aArguments = array()) {

    if (!$result = $this->parseReflector($aArguments)) {

      if (!$this->getType(false) || !$result = $this->getType()->buildReflector($aArguments)) {

        $result = $this->createObject('cached', $aArguments, null, false);
      }
    }

    return $result;
  }

  public function asToken() {

    return "schema:element [{$this->getNamespace()}:{$this->getName()}]";
  }
}

