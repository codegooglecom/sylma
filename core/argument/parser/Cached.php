<?php

namespace sylma\core\argument\parser;
use sylma\core, sylma\storage\fs;

class Cached extends core\argument\Readable {

  const FILE_MANAGER_ALIAS = 'fs';
  const ARGUMENTS_MANAGER = 'argument/parser';

  public function __construct(array $aContent, core\argument $parent = null) {

    parent::__construct($aContent, array(), $parent);
  }

  protected function callFunction(\Closure $function) {

    return $function(\Sylma::getManager(self::FILE_MANAGER_ALIAS));
  }

  public function &getValue($sPath = null, $bDebug = true) {

    $val = parent::getValue($sPath, $bDebug);
    $closure = $this->checkClosure($val);

    $mResult = $closure ? $closure : $val;

    return $mResult;
  }

  protected function checkClosure($mValue) {

    $result = null;

    if ($mValue instanceof \Closure) {

      $result = $this->callFunction($mValue);
    }

    return $result;
  }

  public function &parseValue(array &$aPath, &$mValue, $bDebug) {

    if ($mResult = $this->checkClosure($mValue)) {

      prev($aPath);
    }
    else {

      $mResult =& parent::parseValue($aPath, $mValue, $bDebug);
    }

    return $mResult;
  }

  protected function normalizeObjectUnknown($mVar, $iMode) {

    $mResult = null;

    if (is_callable($mVar)) {

      $mResult = $this->normalizeValue($this->callFunction($mVar), $iMode);
    }
    else {

      $mResult = $this->normalizeUnknown($mVar);
    }

    return $mResult;
  }

  protected function createInstance($mPath) {

    return new parent($mPath, $this->getNS(), $this);
  }
}
