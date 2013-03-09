<?php

namespace sylma\schema\xsd\component;
use sylma\core, sylma\dom, sylma\schema\parser, sylma\parser\reflector;

class Element extends parser\component\Element {

  public function parseRoot(dom\element $el) {

    $this->setName($el->readx('@name'));
    //$this->loadNamespace();

    if ($sType = $el->readx('@type', array(), false)) {

      $parser = $this->getParser();
      list($sNamespace, $sName) = $parser->parseName($sType, null, $el);

      $this->setType($this->getParser()->getType($sName, $sNamespace));
    }
    else {

      $this->setType($this->parseComponent($el->getFirst()));
    }
  }

  public function loadNamespace($sNamespace = '') {

    if (!$sNamespace) $sNamespace = $this->getParser()->getTargetNamespace();
    
    $this->setNamespace($sNamespace, 'element');
  }
}

