<?php

namespace sylma\schema\parser\component;
use sylma\core, sylma\schema\parser, sylma\parser\reflector, sylma\parser\languages\common;

class Element extends Basic implements parser\element, common\stringable {

  protected $sName = '';
  protected $type;
  protected $parent;

  protected $iMinOccurs;
  protected $iMaxOccurs;

  protected function setName($sName) {

    $this->sName = $sName;
  }

  public function getName() {

    return $this->sName;
  }

  /**
   * @TODO Bad mix with module\Namespaced
   */
  public function getNamespace($sPrefix = 'element') {

    return parent::getNamespace($sPrefix);
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

  public function getElement($sName, $sNamespace) {

    if (!$this->isComplex()) {

      $this->launchException("Cannot get sub element of simple typed element $sNamespace:$sName", get_defined_vars());
    }

    if ($result = $this->getType()->getElement($sName, $sNamespace)) {

      $result->setParent($this);
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

    return $this->getType()->getElements();
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
}

