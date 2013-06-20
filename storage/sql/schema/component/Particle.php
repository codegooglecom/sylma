<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\schema, sylma\template;

class Particle extends schema\parser\component\Particle {

  protected $bBuilded = false;

  public function loadElements($sNamespace) {

    if ($this->bBuilded) {

      return;
    }

    $this->bBuilded = true;
    $iPosition = 0;

    foreach ($this->queryx("sql:*") as $el) {

      $element = $this->getParser()->parseComponent($el);
      $element->loadNamespace($sNamespace);

      $this->addElement($element, $iPosition);

      $iPosition++;
    }
  }
}

