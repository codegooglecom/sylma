<?php

namespace sylma\core\argument\parser;
use sylma\core, sylma\parser, sylma\storage\fs;

class Manager extends parser\compiler\Builder implements core\factory {

  const PHP_TEMPLATE = 'compiler/basic.xsl';

  public function __construct(core\argument $arguments = null) {

    $this->setDirectory(__FILE__);

    $arguments = $this->createArgument('manager.yml');
    parent::__construct($arguments);
  }

  public function createArguments(fs\file $file, core\argument $parent = null) {

    $result = $this->load($file);
    if ($parent) $result->setParent($parent);

    return $result;
  }
}
