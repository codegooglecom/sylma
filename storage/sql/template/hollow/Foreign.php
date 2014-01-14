<?php

namespace sylma\storage\sql\template\hollow;
use sylma\core, sylma\storage\sql, sylma\storage\fs;

class Foreign extends sql\template\component\Foreign {

  protected function loadElementRef(fs\file $file = null) {

    if ($file) {

      $this->getParser()->changeMode('view');
      $result = parent::loadElementRef($file);
      $this->getParser()->resetMode();
    }
    else {

      $result = parent::loadElementRef();
    }

    return $result;
  }

  public function reflectRead() {

    return null;
  }
}

