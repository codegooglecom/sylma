<?php

namespace sylma\storage\sql\template\hollow;
use sylma\core, sylma\storage\sql, sylma\storage\fs;

class Foreign extends sql\template\component\Foreign {
  
  protected function loadElementRef(fs\file $file) {

    $this->setDirectory(__FILE__);

    $args = $this->getScript('/#sylma/storage/sql/view/manager.xml');
    $handler = $this->getParser();

    $old = $handler->getArguments();
    $class = $this->getScript($args->read("argument/view"))->get('classes/elemented');

    $handler->setArguments($class, false);
    $result = parent::loadElementRef($file);
    $handler->setArguments($old, false);

    return $result;
  }

  public function reflectRead() {

    return null;
  }
}

