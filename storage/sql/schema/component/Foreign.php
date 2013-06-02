<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\dom, sylma\schema, sylma\storage\sql;

class Foreign extends Element implements sql\schema\element {

  const PREFIX = 'sql';

  protected $elementRef;

  public function parseRoot(dom\element $el) {

    $this->setNode($el, false);
    $this->setName($el->readx('@name'));
    //$this->loadNamespace();

    $this->setType($this->getParser()->getType('foreign', $this->getParser()->getNamespace(self::PREFIX)));

    $this->reflectOccurs($el);
    $this->loadOptional();
  }

  protected function loadElementRef() {

    list($sNamespace, $sName) = $this->getParser()->parseName($this->readx('@table', true), $this, $this->getNode());

    $el = $this->getParser()->getElement($sName, $sNamespace);
    $this->setElementRef($el);
  }

  public function getElementRef() {

    if (!$this->elementRef) {

      if (is_null($this->elementRef)) {

        if ($sImport = $this->readx('@import')) {

          $file = $this->getSourceFile($sImport);
          $this->getParser()->addSchema($file->getDocument());

          $this->loadElementRef($file);
        }
        else {

          $this->loadElementRef();
        }
      }
      else {

        $this->throwException('No ref element defined');
      }
    }

    return $this->elementRef;
  }

  public function setElementRef(schema\parser\element $element) {

    $element->setParent($this);
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

