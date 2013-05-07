<?php

namespace sylma\storage\sql\template\handler;
use sylma\core, sylma\dom, sylma\storage\sql, sylma\schema, sylma\template;

class Pather extends Basic {

  const ALL_TOKEN = '*';

  public function parseExpression($sPath) {


  }

  public function parsePaths($sPath) {

    $aPaths = explode(',', $sPath);
    return array_map('trim', $aPaths);
  }

  public function parsePath($sPath) {

    return explode('/', $sPath);
  }

  public function parsePathTokens(sql\template\pathable $source, array $aPaths, $sMode) {

    $aResult = array();

    foreach ($aPaths as $sPath) {

      $aResult[] = $this->parsePathToken($source, $this->parsePath($sPath), $sMode);
    }

    return $aResult;
  }

  public function parsePathToken(sql\template\pathable $source, array $aPath, $sMode) {

    $sPath = array_shift($aPath);

    if ($aMatch = $this->matchVariable($sPath)) {

      $aResult = $this->parseVariable($source, $aMatch, $aPath, $sMode);
    }
    else if ($aMatch = $this->matchAll($sPath)) {

      $aResult = $this->parsePathAll($source, $aPath, $sMode);
    }
    else if ($aMatch = $this->matchContext($sPath)) {

      $aResult = $this->parsePathContext($source, $aMatch, $aPath, $sMode);
    }
    else if ($aMatch = $this->matchFunction($sPath)) {

      $aResult = $this->parsePathFunction($source, $aMatch, $aPath, $sMode);
    }
    else {

      $aResult = $this->parsePathElement($source, $sPath, $aPath, $sMode);
    }

    return $aResult;
  }

  protected function matchAll($sVal) {

    return $sVal === self::ALL_TOKEN;
  }

  protected function matchContext($sVal) {

    preg_match('/^#(\w+)$/', $sVal, $aResult);

    return $aResult;
  }

  protected function matchVariable($sVal) {

    preg_match('/^\$(\w+)$/', $sVal, $aResult);

    return $aResult;
  }

  public function matchFunction($sVal) {

    preg_match('/^(\w+)\\(/', $sVal, $aResult);

    return $aResult;
  }

  protected function parsePathAll(sql\template\pathable $source, array $aPath, $sMode) {

    return $source->reflectApplyAll($aPath, $sMode);
  }

  protected function parseVariable(sql\template\pathable $source, array $aMatch, array $aPath, $sMode) {

    $component = $this->getView()->getCurrentTemplate()->getVariable($aMatch[1]);

    return $component->getVar();
    //return $source->parseVariable($aPath, $aMatch[1], $sMode);
  }

  protected function parsePathElement(sql\template\pathable $source, $sPath, array $aPath, $sMode) {

    list($sNamespace, $sName) = $this->parseName($sPath, $source, $source->getNode(false));

    $element = $source->getElement($sName, $sNamespace);

    return $element ? $element->reflectApplyPath($aPath, $sMode) : null;
  }

  protected function parsePathContext(sql\template\pathable $source, array $aMatch, array $aPath, $sMode) {

    switch ($aMatch[1]) {

      case 'element' :

        $result = $source->reflectApplyPath($aPath, $sMode);
        break;

      case 'type' :

        $type = $source->getType();
        $type->setElementRef($source);

        $result = $type->reflectApplyPath($aPath, $sMode);
        break;

      default :

        $this->launchException('Unknown context', get_defined_vars());
    }

    return $result;
  }

  protected function parsePathFunction(sql\template\pathable $source, array $aMatch, array $aPath, $sMode) {

    return $source->reflectApplyFunction($aMatch[1], $aPath, $sMode);
  }
}

