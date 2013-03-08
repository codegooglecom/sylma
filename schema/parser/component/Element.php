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

  public function getParent($bDebug = true) {

    if (!$this->parent && $bDebug) {

      $this->throwException('No parent');
    }

    return $this->parent;
  }

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

    $result = $this->getType()->getElement($sName, $sNamespace);
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

  protected function getMaxOccurs($bBool) {

    return $bBool ? $this->iMaxOccurs == 'n' || $this->iMaxOccurs > 1 : $this->iMaxOccurs;
  }

  public function asString() {

    return $this->getParent()->asString() . '.`' . $this->getName() . "`";
  }
}

