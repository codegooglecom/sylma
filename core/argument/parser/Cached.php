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

    if (is_callable($mResult)) $mResult = self::callFunction($mResult);

    return $mResult;
  }

  protected static function callFunction(\Closure $function) {

    return $function(\Sylma::getControler(self::FILE_MANAGER_ALIAS));
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

  protected static function normalizeObjectUnknown($mVar, $iMode) {

    $mResult = null;

    if (is_callable($mVar)) {

      $mResult = self::normalizeValue(self::callFunction($mVar), $iMode);
    }
    else {

      $mResult = parent::normalizeUnknown($mVar);
    }

    return $mResult;
  }
}
