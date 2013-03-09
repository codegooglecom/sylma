<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\schema, sylma\template;

class Particle extends schema\parser\component\Particle {

  protected function loadElements() {

    foreach ($this->queryx("sql:*") as $el) {

      $element = $this->getParser()->parseComponent($el);
      //$element->loadNamespace

      $element->loadNamespace();
      $this->addElement($element);
    }
  }

  public function getElement($sName, $sNamespace) {

    $result = null;

    if (!$result = parent::getElement($sName, $sNamespace)) {

      if ($el = $this->getx("sql:*[@name='$sName']")) {

        $result = $this->getParser()->parseComponent($el);
        $result->loadNamespace($sNamespace);

        $this->addElement($result);
      }
    }

    return $result;
  }

  public function getElements() {

    $this->loadElements();

    return parent::getElements();
  }
}

