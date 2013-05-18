<?php

namespace sylma\storage\sql\template\handler;
use sylma\core, sylma\dom, sylma\storage\sql, sylma\schema, sylma\template;

class Basic extends sql\schema\Handler {

  protected $var;
  protected $query;
  protected $template;
  protected $view;

  protected $aTemplates = array();

  public function getView() {

    return $this->view;
  }

  public function setView(template\parser\handler\Domed $view) {

    $this->view = $view;
  }

  public function lookupTemplate(schema\parser\element $element, $sContext, $sMode, $bRoot = false) {

    $iLast = 0;
    $result = null;

    foreach ($this->getTemplates() as $template) {

      if ($this->getView()->checkTemplate($template, false)) continue;

      $iWeight = $template->getWeightSchema($element, $sContext, $sMode, $bRoot);
      if ($iWeight && $iWeight >= $iLast) {

        $result = $template;
        $iLast = $iWeight;
      }
    }

    if ($result && $result->getMatch()) {

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

  public function createCollection() {

    return $this->loadSimpleComponent('component/collection');
  }

  public function getPather() {

    return $this->getView()->getCurrentTemplate()->getPather();
  }

  public function parsePathToken(sql\template\pathable $source, array $aPath, $sMode) {

    $pather = $this->getPather();
    $pather->setSource($source);

    return $pather->parsePathToken($aPath, $sMode);
  }

  public function parsePath(sql\template\pathable $source, $sPath, $sMode) {

    $pather = $this->getPather();
    $pather->setSource($source);

    return $pather->applyPath($sPath, $sMode);
  }

  public function reflectApplyDefault(schema\parser\container $source, $sPath, array $aPath, $sMode) {

    list($sNamespace, $sName) = $this->parseName($sPath);

    $element = $source->getElement($sName, $sNamespace);

    return $element ? $element->reflectApplyPath($aPath, $sMode) : null;
  }
}

