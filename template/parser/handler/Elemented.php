<?php

namespace sylma\template\parser\handler;
use sylma\core, sylma\template;

class Elemented extends Domed {

  protected $aElements = array();

  public function getElement() {

    if (!$this->aElements) {

      $this->launchException('No element defined');
    }

    return end($this->aElements);
  }

  public function startElement(template\element $el) {

    $this->aElements[] = $el;
  }

  public function stopElement() {

    array_pop($this->aElements);
  }
}

