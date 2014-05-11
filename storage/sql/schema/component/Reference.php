<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\dom, sylma\schema, sylma\storage\sql;

class Reference extends Foreign implements sql\schema\reference {

  public function parseRoot(dom\element $el) {

    $this->setNode($el, false);

    $this->loadName();
    $this->loadType();

    //$this->reflectOccurs($el);
    //$this->loadOptional();
  }

  protected function loadType() {

    $this->setType($this->getParser()->getType('reference', $this->getParser()->getNamespace(self::PREFIX)));
  }

  public function filterQuery(sql\query\parser\Basic $query) {

    $query->setWhere($this->getForeign(), '=', $this->getParent()->getElementArgument('id'));
  }
}

