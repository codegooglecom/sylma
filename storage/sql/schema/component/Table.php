<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\dom, sylma\storage\sql\schema;

class Table extends Element implements schema\table {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->setName($el->readx('@name'));

    $parser = $this->getParser();
    $type = $this->loadSimpleComponent('component/complexType', $parser);
    $type->loadIdentity();
    $particle = $this->loadComponent('component/particle', $el, $parser);

    $type->addParticle($particle);
    $this->setType($type);
  }

  public function loadNamespace($sNamespace = '') {

    parent::loadNamespace($sNamespace);
    if ($this->getType(false)) $this->getType()->loadElements($this->getNamespace());
  }

  public function getConnectionAlias() {

    return $this->readx('@connection');
  }

  protected function getCharset() {

    return $this->readx('@charset');
  }

  public function asString() {

    return "`" . $this->getName() . "`";
  }

}

