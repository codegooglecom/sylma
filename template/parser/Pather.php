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

    return $this->buildExpression($aResult);
  }

  protected function buildExpression($aContent) {

    $window = $this->getWindow();

    return $window->createCondition($aContent);
  }

  protected function matchString($sValue) {

    return $sValue{0} == "'" ? substr($sValue, 1, -1) : null;
  }

  protected function matchNumeric($sValue) {

    return is_numeric($sValue);
  }

  public function applyPath($sPath, $sMode) {

    $aPaths = $this->parsePaths($sPath);
    return $this->parsePathTokens($aPaths, $sMode);
  }

  protected function parsePaths($sPath) {

    $aPaths = explode(',', $sPath);
    return array_map('trim', $aPaths);
  }

  protected function parsePath($sPath) {

    return explode('/', $sPath);
  }

  public function parsePathTokens(array $aPaths, $sMode) {

    $aResult = array();

    foreach ($aPaths as $sPath) {

      $aResult[] = $this->parsePathToken($this->parsePath($sPath), $sMode);
    }

    return $aResult;
  }

  public function parsePathToken(array $aPath, $sMode) {

    return $this->parsePathTokenValue(array_shift($aPath), $aPath, $sMode);
  }

  protected function parsePathTokenValue($sPath, array $aPath, $sMode) {

    if ($aMatch = $this->matchVariable($sPath)) {

      $aResult = $this->parseVariable($aMatch, $aPath, $sMode);
    }
    else if ($aMatch = $this->matchAll($sPath)) {

      $aResult = $this->parsePathAll($aPath, $sMode);
    }
    else if ($aMatch = $this->matchFunction($sPath)) {

      $aResult = $this->parsePathFunction($aMatch, $aPath, $sMode);
    }
    else {

      $aResult = $this->parsePathDefault($sPath, $aPath, $sMode);
    }

    return $aResult;
  }

  protected function parsePathDefault($sPath, array $aPath, $sMode) {

    $this->launchException('No default action defined');
  }

  protected function matchAll($sVal) {

    return $sVal === self::ALL_TOKEN;
  }

  protected function matchVariable($sVal) {

    preg_match('/^\$(\$?\w+)$/', $sVal, $aResult);

    return $aResult;
  }

  protected function matchFunction($sVal) {

    preg_match('/^([\w-]+)\\(/', $sVal, $aResult);

    return $aResult;
  }

  protected function parsePathAll(array $aPath, $sMode) {

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

  protected function parsePathFunction(array $aMatch, array $aPath, $sMode) {

    $this->launchException('Not yet implemented');
  }
}

