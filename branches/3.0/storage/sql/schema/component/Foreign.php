<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\dom, sylma\schema, sylma\storage\sql;

class Foreign extends Element implements sql\schema\foreign {

  const PREFIX = 'sql';

  protected $elementRef;

  public function parseRoot(dom\element $el) {

    $this->setNode($el, false);

    $this->loadName();
    $this->loadType();

    $this->reflectOccurs($el);
    $this->loadOptional();
  }

  protected function loadName() {

    $this->setName($this->readx('@name'));
  }

  protected function loadType() {

    $this->setType($this->getParser()->getType('foreign', $this->getParser()->getNamespace(self::PREFIX)));
  }

  protected function loadElementRef() {

    list($sNamespace, $sName) = $this->parseName($this->readx('@table', true));

    if ($result = $this->getParser()->getElement($sName, $sNamespace, false)) {

      $result->setParent($this);
    }

    return $result;
  }

  protected function importElementRef() {

    if (!$result = $this->loadElementRef()) {

      $file = $this->getSourceFile($this->readx('@import', true));
      $this->getParser()->addSchema($file->getDocument());

      $result = $this->loadElementRef($file);
    }

    return $result;
  }

  public function getElementRef() {

    if (!$this->elementRef) {

      if (is_null($this->elementRef)) {

        $this->setElementRef($this->importElementRef());
      }
      else {

        $this->throwException('No ref element defined');
      }
    }

    return $this->elementRef;
  }

  public function setElementRef(Table $element) {

    $this->elementRef = $element;
  }

  protected function reflectOccurs(dom\element $el) {

    if (!$sOccurs = $el->readx('@occurs', array(), false)) {

      $sOccurs = '1..1';
    }

    list($iMin, $iMax) = explode('..', $sOccurs);
    $this->setOccurs($iMin, $iMax);
  }
}
