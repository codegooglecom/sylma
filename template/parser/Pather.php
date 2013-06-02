<?php

namespace sylma\template\parser;
use sylma\core;

class Pather extends component\Child {

  const ALL_TOKEN = '*';

  protected $source;
  protected $aOperators = array('<', '>', '=', '!=', 'and', 'or', '+', '*', '/');

  public function setSource($source) {

    if (!is_object($source)) {

      $this->launchException('SET : Bad source', get_defined_vars());
    }

    $this->source = $source;
  }

  protected function getSource() {

    if (!$this->source) {

      $this->launchException('No source defined');
    }

    return $this->source;
  }

  protected function getOperators() {

    return $this->aOperators;
  }

  public function parseExpression($sPath) {

    $aResult = array();

    $aTokens = explode(' ', $sPath);
    $window = $this->getWindow();

    $bOp = false; // alternate between op and val

    foreach ($aTokens as $sToken) {

      if ($bOp) {

        if (!in_array($sToken, $this->getOperators())) {

          $this->launchException("Unknown operator : {$sToken}");
        }

        if ($sToken == '=') $sToken = '==';
        $aResult[] = $window->createOperator($sToken);

        $bOp = false;
      }
      else {

        $bNot = false;

        if ($sToken{0} == '!') {

          $bNot = true;
          $sToken = (trim(substr($sToken, 1)));
        }

        if ($sValue = $this->matchString($sToken)) {

          $result = $window->createString($sValue);
        }
        else if ($this->matchNumeric($sToken)) {

          $result = $window->createNumeric($sToken);
        }
        else {

          $result = $this->applyPath($sToken, '');
        }

        if ($bNot) $result = $window->createNot($result);
        $aResult[] = $result;

        $bOp = true;
      }
    }

    return $aResult;
  }

  protected function matchString($sValue) {

    return $sValue && $sValue{0} == "'" ? substr($sValue, 1, -1) : null;
  }

  protected function matchNumeric($sValue) {

    return is_numeric($sValue);
  }

  public function applyPath($sPath, $sMode, array $aArguments = array()) {

    if ($sPath) {

      $sPath = trim($sPath);

      if ($this->matchAll($sPath)) {

        $aResult = $this->parsePathAll($sPath, $sMode, $aArguments);
      }
      else {

        $aResult = $this->parsePathTokens($this->parsePaths($sPath), $sMode, false, $aArguments);
      }
    }
    else {

      $aResult = $this->getSource()->reflectApply($sMode, $aArguments);
    }

    return $aResult;
  }

  public function readPath($sPath, $sMode) {

    $sPath = trim($sPath);
    $aResult = $this->parsePathTokens($this->parsePaths($sPath), $sMode, true);

    return $aResult;
  }

  protected function parsePaths($sPath) {

    $aPaths = explode(',', $sPath);
    return array_map('trim', $aPaths);
  }

  protected function parsePath($sPath) {

    return explode('/', $sPath);
  }

  public function parsePathTokens(array $aPaths, $sMode, $bRead = false, array $aArguments = array()) {

    $aResult = array();

    foreach ($aPaths as $sPath) {

      $aResult[] = $this->parsePathToken($this->parsePath($sPath), $sMode, $bRead, $aArguments);
    }

    return $aResult;
  }

  public function parsePathToken(array $aPath, $sMode, $bRead, array $aArguments = array()) {

    return $this->parsePathTokenValue(array_shift($aPath), $aPath, $sMode, $bRead, $aArguments);
  }

  protected function parsePathTokenValue($sPath, array $aPath, $sMode, $bRead, array $aArguments = array()) {

    if ($aMatch = $this->matchVariable($sPath)) {

      $aResult = $this->parseVariable($aMatch, $aPath, $sMode);
    }
    else if ($aMatch = $this->matchFunction($sPath)) {

      $aResult = $this->parsePathFunction($aMatch, $aPath, $sMode, $bRead, $aArguments);
    }
    else {

      $aResult = $this->parsePathDefault($sPath, $aPath, $sMode, $bRead, $aArguments);
    }

    return $aResult;
  }

  protected function parsePathDefault($sPath, array $aPath, $sMode, $bRead, array $aArguments = array()) {

    $this->launchException('No default action defined');
  }

  protected function matchAll($sVal) {

    return $sVal{0} === self::ALL_TOKEN;
  }

  protected function matchVariable($sVal) {

    preg_match('/^\$(\$?[\w-]+)$/', $sVal, $aResult);

    return $aResult;
  }

  protected function matchFunction($sVal) {

    //preg_match('/^([\w-]+)\\(/', $sVal, $aResult);
    preg_match('/^([\w-]+)\\((.*)\\)$/', $sVal, $aResult);

    return $aResult;
  }

  public function parseArguments($sArguments, $sMode, $bRead, $bApply = true) {

    $aResult = array();

    if ($sArguments) {

      foreach (explode(',', $sArguments) as $sArgument) {

        if (strpos($sArgument, '=') !== false) {

          list($mKey, $sValue) = explode('=', $sArgument);
        }
        else {

          $mKey = count($aResult);
          $sValue = $sArgument;
        }

        if ($sString = $this->matchString($sValue)) {

          $aResult[$mKey] = $sString;
        }
        else if ($this->matchNumeric($sValue)) {

          $aResult[$mKey] = $sValue;
        }
        else {

          $aResult[$mKey] = $bApply ? $this->applyPath($sValue, $sMode) : $this->parseArgumentDefault($sValue, $sMode, $bRead, $bApply);
        }
      }
    }

    return $aResult;
  }

  protected function parseArgumentDefault($sValue, $sMode, $bRead, $bApply) {

    $this->launchException('No behaviour defined');
  }

  protected function parsePathAll($sPath, $sMode, array $aArguments = array()) {

    $this->launchException('Not yet implemented');
  }

  protected function parseVariable(array $aMatch, array $aPath, $sMode) {

    $sName = $aMatch[1];

    if ($sName{0} === '$') {

      $result = $this->getParser()->getConstant(substr($sName, 1));
    }
    else {

      $result = $this->getTemplate()->getVariable($sName)->getVar();
    }

    return $result;
    //return $source->parseVariable($aPath, $aMatch[1], $sMode);
  }

  protected function parsePathFunction(array $aMatch, array $aPath, $sMode, $bRead, array $aArguments = array()) {

    $this->launchException('Not yet implemented');
  }
}

