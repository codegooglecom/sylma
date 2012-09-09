<?php

namespace sylma\core\argument\parser;
use sylma\core, sylma\storage\fs;

\Sylma::load('../Iterator.php', __DIR__);

class Cached extends core\argument\Iterator {

  public function __construct(fs\file $file, core\argument $parent = null) {

    parent::__construct($this->loadFile($file), array(), $parent);
  }

  protected function loadFile(fs\file $file) {

    return include($file->getRealPath());
  }

  public function parseValue($mValue, array $aParentPath = array()) {

    if (is_callable($mValue)) {

      $mResult = $mValue();
    }
    else {

      $mResult = parent::parseValue($mValue, $aParentPath);
    }

    return $mResult;
  }
}
