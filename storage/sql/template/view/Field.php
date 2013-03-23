<?php

namespace sylma\storage\sql\template\view;
use sylma\core, sylma\storage\sql\template\component, sylma\template, sylma\schema\parser;

class Field extends component\Field {

  protected function reflectApplySelf($sMode = '') {

    if ($result = parent::reflectApplySelf($sMode)) {

      $this->addToQuery();
    }
    else {

      $result = $this->reflectRead();
    }

    return $result;
  }

  protected function addToQuery() {

    $query = $this->getQuery();
    $query->setColumn($this);
  }

  public function reflectRead() {

    $this->addToQuery();

    return $this->reflectSelf();
  }
}

