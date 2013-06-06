<?php

namespace sylma\storage\sql\template\view;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common, sylma\schema\parser;

class Foreign extends sql\template\component\Foreign {

  protected function addToQuery() {

    $this->getParent()->addElementToQuery($this);
  }

  public function reflectRead() {

    $this->addToQuery();

    return $this->getParent()->getSource()->call('read', array($this->getName()), 'php-string');
  }

}

