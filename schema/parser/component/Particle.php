<?php

namespace sylma\schema\parser\component;
use sylma\core, sylma\schema\parser, sylma\parser\reflector;

abstract class Particle extends Basic implements parser\particle {

  protected $aElements = array();

  public function addElement(parser\element $element) {

    $this->aElements[$element->getNamespace()][$element->getName()] = $element;
  }

  protected function getElement($sName, $sNamespace) {

    return isset($this->aElements[$sNamespace][$sName]) ? $this->aElements[$sNamespace][$sName] : null;
  }
}

