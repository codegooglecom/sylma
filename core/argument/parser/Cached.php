<?php

namespace sylma\core\argument\parser;
use sylma\core, sylma\storage\fs;

class Cached extends core\argument\Readable {

  const FILE_MANAGER_ALIAS = 'fs';
  const ARGUMENTS_MANAGER = 'argument/parser';

  public function __construct(array $aContent, core\argument $parent = null) {

    parent::__construct($aContent, array(), $parent);
  }

  protected static function callFunction(\Closure $function) {

    return $function(\Sylma::getControler(self::FILE_MANAGER_ALIAS));
  }

  public function getValue($sPath = null, $bDebug = true) {

    $result = parent::getValue($sPath, $bDebug);
    $closure = $this->checkClosure($result);

    return $closure ? $closure : $result;
  }

  protected function checkClosure($mValue) {

    $result = null;

    if ($mValue instanceof \Closure) {

      $result = $this->callFunction($mValue);
    }

    return $result;
  }

  public function parseValue(array &$aPath, $mValue, $bDebug) {

    if ($mResult = $this->checkClosure($mValue)) {

      prev($aPath);
    }
    else {

      $mResult = parent::parseValue($aPath, $mValue, $bDebug);
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
