<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\dom, sylma\schema, sylma\storage\fs;

class Foreign extends Element {

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

  protected function loadElementRef(fs\file $file) {

    $this->getParser()->addSchema($file->getDocument());

    list($sNamespace, $sName) = $this->getParser()->parseName($this->readx('@table', true), $this, $this->getNode());

    return $this->getParser()->getElement($sName, $sNamespace);
  }

  public function getElementRef() {

    if (!$this->elementRef) {

      if (is_null($this->elementRef)) {

        if ($sImport = $this->readx('@import')) {

          $file = $this->getSourceFile($sImport);
          $this->setElementRef($this->loadElementRef($file));
        }
        else {

          $this->launchException('Not yet implemented');
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

