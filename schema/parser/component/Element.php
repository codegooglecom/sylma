<?php

namespace sylma\schema\parser\component;
use sylma\core, sylma\dom, sylma\schema, sylma\parser\languages\common;

class Element extends Basic implements schema\parser\element, common\stringable, core\tokenable {

  protected $type;
  protected $parent;
  protected $particle;

  protected $iPosition = 0;

  protected $iMinOccurs;
  protected $iMaxOccurs;

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

  public function getType() {

    if (!$this->type) {

      $this->throwException('No type defined');
    }

    return $this->type;
  }

  public function setType(schema\parser\type $type) {

    $this->type = $type;
  }

  public function getElement($sName, $sNamespace) {

    if (!$this->isComplex()) {

      $this->launchException("Cannot get sub element of simple typed element $sNamespace:$sName", get_defined_vars());
    }

    if ($result = $this->getType()->getElement($sName, $sNamespace)) {

      $this->loadChild($result);
    }
    else {

      $this->launchException("Cannot find element $sNamespace:$sName", get_defined_vars());
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
  }

  public function isComplex() {

    return $this->getType()->isComplex();
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

  public function asString() {

    return $this->getParent()->asString() . '.`' . $this->getName() . "`";
  }

  public function asToken() {

    return "schema:element [{$this->getNamespace()}:{$this->getName()}]";
  }
}

