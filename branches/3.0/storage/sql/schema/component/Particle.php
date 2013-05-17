<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\schema, sylma\template;

class Particle extends schema\parser\component\Particle {

  protected function loadElements() {

    // TODO, single element loaded with getElement() are erased !

    $iPosition = 0;

    foreach ($this->queryx("sql:*") as $el) {

      $element = $this->getParser()->parseComponent($el);
      //$element->loadNamespace

      $element->loadNamespace();
      $this->addElement($element, $iPosition);
      $iPosition++;
    }
  }

  public function getElement($sName, $sNamespace) {

    $result = null;

    if (!$result = parent::getElement($sName, $sNamespace)) {

      $sPath = "sql:*[@name='$sName']";

      if ($el = $this->getx($sPath)) {

        $result = $this->getParser()->parseComponent($el);
        $result->loadNamespace($sNamespace);

        $iPosition = $this->readx($sPath . '[position()]');
        $this->addElement($result, $iPosition);
      }
    }

    return $result;
  }

  public function getElements() {

    $this->loadElements();

    return parent::getElements();
  }
}

