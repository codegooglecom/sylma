<?php

namespace sylma\core\argument\parser;
use sylma\core, sylma\parser, sylma\storage\fs;

class Manager extends parser\compiler\Builder {

  const PHP_TEMPLATE = 'compiler/basic.xsl';

  public function __construct() {

    $this->setDirectory(__FILE__);

    $this->loadDefaultArguments();
    $this->setArguments('manager.yml');
  }

  public function createArguments(fs\file $file, core\argument $parent = null) {

    $result = $this->load($file);
    if ($parent) $result->setParent($parent);

    return $result;
  }
}
