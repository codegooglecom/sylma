<?php

namespace sylma\core\argument\parser;
use sylma\core, sylma\storage\fs;

class Cached extends core\argument\Domed {

  const FILE_MANAGER_ALIAS = 'fs';
  const ARGUMENTS_MANAGER = 'argument/parser';

  public function __construct(array $aContent, core\argument $parent = null) {

    parent::__construct($aContent, array(), $parent);
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

  protected function createInstance($mPath) {

    return new parent($mPath, $this->getNS(), $this);
  }
}
