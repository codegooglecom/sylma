<?php

namespace sylma\storage\sql\template;
use sylma\core, sylma\dom, sylma\storage\sql, sylma\schema, sylma\template;

class Handler extends sql\schema\Handler {

  const ALL_TOKEN = '*';

  protected $var;
  protected $query;
  protected $template;
  protected $view;

  protected $aTemplates = array();

  public function getView() {

    return $this->view;
  }

  public function setView(template\parser\Elemented $view) {

    $this->view = $view;
  }

  public function lookupTemplate(schema\parser\element $element, $sContext, $sMode) {

    $iLast = 0;
    $result = null;

    foreach ($this->getTemplates() as $template) {

      $iWeight = $template->getWeight($element, $sContext, $sMode);
      if ($iWeight && $iWeight >= $iLast) {

        $result = $template;
        $iLast = $iWeight;
      }
    }

    if ($result) {

      $result = clone $result;
    }

    return $result;
  }

  protected function getTemplates() {

    return $this->aTemplates;
  }

  public function loadTemplates(array $aTemplates = array()) {

    $this->aTemplates = $aTemplates;
  }

  public function lookupNamespace($sPrefix = 'target', dom\element $context = null) {

    if (!$sPrefix) $sPrefix = self::TARGET_PREFIX;

    if (!$sNamespace = parent::lookupNamespace($sPrefix, $context) and $sPrefix) {

      $sNamespace = $this->getView()->lookupNamespace($sPrefix);
    }

    return $sNamespace;
  }

  public function parsePaths($sPath) {

    $aPaths = explode(',', $sPath);
    return array_map('trim', $aPaths);
  }

  public function parsePath($sPath) {

    return explode('/', $sPath);
  }

  public function parsePathTokens($source, array $aPaths, $sMode) {

    $aResult = array();

    foreach ($aPaths as $sPath) {

      $aResult[] = $this->parsePathToken($source, $this->parsePath($sPath), $sMode);
    }

    return $aResult;
  }

  public function parsePathToken($source, array $aPath, $sMode) {

    $sPath = array_shift($aPath);

    if ($aMatch = $this->matchAll($sPath)) {

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

  public function matchAll($sVal) {

    return $sVal === self::ALL_TOKEN;
  }

  public function matchContext($sVal) {

    preg_match('/^#(\w+)$/', $sVal, $aResult);

    return $aResult;
  }

  public function matchFunction($sVal) {

    preg_match('/^(\w+)\\(/', $sVal, $aResult);

    return $aResult;
  }

  public function parsePathAll($source, array $aPath, $sMode) {

    $aResult = array();

    foreach ($source->getElements() as $element) {

      $element->setParent($source);
      $aResult[] = $element->reflectApplyPath($aPath, $sMode);
    }

    return $aResult;
  }

  public function parsePathElement($source, $sPath, array $aPath, $sMode) {

    list($sNamespace, $sName) = $this->parseName($sPath, $source, $source->getNode());

    $element = $source->getElement($sName, $sNamespace);

    return $element ? $element->reflectApplyPath($aPath, $sMode) : null;
  }

  public function parsePathContext($source, array $aMatch, array $aPath, $sMode) {

    switch ($aMatch[1]) {

      case 'element' : $result = $source->reflectApplyPath($aPath, $sMode); break;
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

  public function parsePathFunction($source, array $aMatch, array $aPath, $sMode) {

    return $source->reflectApplyFunction($aMatch[1], $aPath, $sMode);
  }
}

