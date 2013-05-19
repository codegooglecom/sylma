<?php

namespace sylma\schema\parser\component;
use sylma\core, sylma\schema\parser, sylma\parser\reflector;

abstract class Particle extends Basic implements parser\particle {

  protected $aElements = array();
  protected $aElementsIndexed = array();

  public function addElement(parser\element $element, $iPosition = null) {

    if (!$element->getNamespace()) {

      $this->launchException('Element not valid, no namespace defined');
    }

    $element->setPosition($iPosition);
    $element->setParticle($this);

    $this->aElements[$element->getNamespace()][$element->getName()] = $element;

    if (is_null($iPosition)) $iPosition = count($this->aElementsIndexed);
    $this->aElementsIndexed[$iPosition] = $element;
  }

  public function getElement($sName, $sNamespace) {

    return isset($this->aElements[$sNamespace][$sName]) ? $this->aElements[$sNamespace][$sName] : null;
  }

  public function getElements() {

    $aResult = array();

    foreach ($this->aElements as $aElements) {

      $aResult = array_merge($aResult, $aElements);
    }

    return $aResult;
  }

  public function getElementFromIndex($iPosition, $bDebug) {

    if (!isset($this->aElementsIndexed[$iPosition])) {

      if ($bDebug) $this->launchException('No element found at this position');
      $result = null;
    }
    else {

      $result = $this->aElementsIndexed[$iPosition];
    }

    return $result;
  }
}

