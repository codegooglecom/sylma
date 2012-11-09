<?php

namespace sylma\core\argument\parser;
use sylma\core, sylma\storage\fs;

class Cached extends core\argument\Iterator {

  const FILE_MANAGER_ALIAS = 'fs';

  public function __construct(fs\file $file, core\argument $parent = null) {

    parent::__construct($this->loadFile($file), array(), $parent);
  }

  protected function loadFile(fs\file $file) {

    $mResult = include($file->getRealPath());

    if (is_callable($mResult)) $mResult = $this->callFunction($mResult);

    return $mResult;
  }

  public function getFile($sPath) {

    $fs = \Sylma::getControler(self::FILE_MANAGER_ALIAS);

    return $fs->getFile($sPath);
  }

  protected function callFunction(\Closure $function) {

    return $function($this);
  }

  public function parseValue($mValue, array $aParentPath = array()) {

    if (is_callable($mValue)) {

      $mResult = $this->callFunction($mValue);
    }
    else {

      $mResult = parent::parseValue($mValue, $aParentPath);
    }

    return $mResult;
  }
}
