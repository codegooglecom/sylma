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

    return $result;
  }

  protected function getTemplates() {

    return $this->aTemplates;
  }

  public function loadTemplates(array $aTemplates = array()) {

    $this->aTemplates = $aTemplates;
  }

  public function lookupNamespace($sPrefix = 'target', dom\element $context = null) {

    if (!$sNamespace = parent::lookupNamespace($sPrefix, $context)) {

      $sNamespace = $this->getView()->lookupNamespace($sPrefix);
    }

    return $sNamespace;
  }

  public function parsePath($sPath) {

    return explode('/', $sPath);
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

  protected function matchAll($sVal) {

    return $sVal === self::ALL_TOKEN;
  }

  protected function matchContext($sVal) {

    preg_match('/^#(\w+)$/', $sVal, $aResult);

    return $aResult;
  }

  protected function matchFunction($sVal) {

    preg_match('/^(\w+)\\(/', $sVal, $aResult);

    return $aResult;
  }

  protected function parsePathAll($source, array $aPath, $sMode) {

    $aResult = array();

    foreach ($source->getElements() as $element) {

      $element->setParent($source);
      $aResult[] = $element->reflectApplyPath($aPath, $sMode);
    }

    return $aResult;
  }

  protected function parsePathElement($source, $sPath, array $aPath, $sMode) {

    list($sNamespace, $sName) = $this->parseName($sPath, $source, $source->getNode());

    $element = $source->getElement($sName, $sNamespace);
    //$element->setParent($source);

    return $element->reflectApplyPath($aPath, $sMode);
  }

  protected function parsePathContext($source, array $aMatch, array $aPath, $sMode) {

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

  protected function parsePathFunction($source, array $aMatch, array $aPath, $sMode) {

    return $source->reflectApplyFunction($aMatch[1], $aPath, $sMode);
  }
}

