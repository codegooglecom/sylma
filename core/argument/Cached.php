<?php

namespace sylma\core\argument;
use sylma\core, sylma\storage\fs;

\Sylma::load('Iterator.php', __DIR__);

class Cached extends Iterator {

  public function __construct(fs\file $file, core\argument $parent = null) {

    parent::__construct($this->loadFile($file), array(), $parent);
  }

  protected function loadFile(fs\file $file) {

    return include($file->getRealPath());
  }

  public function normalizeUnknown($mVar) {

    if (is_callable($mVar)) {

      $mResult = $mVar();
    }
    else {

      $mResult = parent::normalizeUnknown($mVar);
    }

    return $mResult;
  }
}
