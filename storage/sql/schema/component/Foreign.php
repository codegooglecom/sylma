<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\dom, sylma\schema;

class Foreign extends schema\xsd\component\Element {

  protected $elementRef;

  public function getElementRef() {

    if (!$this->elementRef) {

      $this->throwException('No ref element defined');
    }

    return $this->elementRef;
  }

  public function setElementRef(schema\parser\element $element) {

    $element->setParent($this);
    $this->elementRef = $element;
  }

  public function parseRoot(dom\element $el) {

    $this->setName($el->readx('@name'));

    if ($sImport = $el->readx('@import', array(), false)) {

      $file = $this->getSourceFile($sImport);
      $this->getParser()->addSchema($file->getDocument());
    }

    if ($sElement = $el->readx('@table', array(), false)) {

      $this->setElementRef($this->getParser()->getElement($sElement, $el));
    }

    $this->reflectOccurs($el);
  }

  protected function reflectOccurs(dom\element $el) {

    if (!$sOccurs = $el->readx('@occurs', array(), false)) {

      $sOccurs = '1..1';
    }

    list($iMin, $iMax) = explode('..', $sOccurs);
    $this->setOccurs($iMin, $iMax);
  }
}

