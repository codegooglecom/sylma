<?php

namespace sylma\schema\parser\component;
use sylma\core, sylma\schema\parser, sylma\parser\reflector, sylma\parser\languages\common;

class Element extends Basic implements parser\element, common\stringable {

  protected $sName = '';
  protected $type;
  protected $parent;

  protected $iMinOccurs;
  protected $iMaxOccurs;

  public function setParent(parser\element $parent) {

    $this->parent = $parent;
  }

  public function getParent() {

    return $this->parent;
  }

  protected function setName($sName) {

    $this->sName = $sName;
  }

  public function getName() {

    return $this->sName;
  }

  public function getType() {

    if (!$this->type) {

      $this->throwException('No type defined');
    }

    return $this->type;
  }

  public function setType(parser\type $type) {

    $this->type = $type;
  }

  public function getElement($sName) {

    $result = $this->getType()->getElement($sName);
    $result->setParent($this);

    return $result;
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

  protected function getMaxOccurs() {

    return $this->iMaxOccurs;
  }

  public function asString() {

    return $this->getName();
  }
}

