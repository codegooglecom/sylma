<?php

namespace sylma\core\argument\parser;
use sylma\core, sylma\parser\compiler, sylma\storage\fs;

class Manager extends compiler\Builder {

  protected static $sArgumentClass = 'sylma\core\argument\Filed';

  public function __construct(core\argument $arg = null) {

    parent::__construct($arg);

    $this->setDirectory(__FILE__);
    $this->setArguments('manager.yml');
  }

  public function createArguments(fs\file $file, core\argument $parent = null) {

    $result = $this->load($file);
    if ($parent) $result->setParent($parent);

    return $result;
  }
}
