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

    if (!$this->view) {

      $this->launchException('No view defined');
    }

    return $this->view;
  }

  public function setView(template\parser\handler\Domed $view) {

    $this->view = $view;
  }

  public function lookupTemplate(schema\parser\element $element, $sContext, $sMode, $bRoot = false) {

    $result = null;
    $current = (object) array(
      'template' => null,
      'weight' => 0,
      'key' => 0,
    );

    foreach ($this->getTemplates() as $iKey => $template) {

      if ($this->getView()->checkTemplate($template, $element->asToken(), false)) continue;

      $iWeight = $template->getWeightSchema($element, $sContext, $sMode, $bRoot);
      if ($iWeight && $iWeight >= $current->weight) {

        $current->template = $template;
        $current->weight = $iWeight;
        $current->key = $iKey;
      }
    }

    if ($current->template) {

      $result = $this->loadTemplate($current->template, $current->key);
    }

    return $result;
  }

  protected function loadTemplate(template\parser\template $tpl, $iKey) {

    if ($tpl->useOnce()) {

      unset($this->aTemplates[$iKey]);
    }

    $result = clone $tpl;

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

  public function parsePathToken(sql\template\pathable $source, array $aPath, $sMode, $bRead = false, array $aArguments = array()) {

    $pather = $this->getPather();
    $pather->setSource($source);

    return $pather->parsePathToken($aPath, $sMode, $bRead, $aArguments);
  }

  public function parsePath(sql\template\pathable $source, $sPath, $sMode, array $aArguments = array()) {

    $pather = $this->getPather();
    $pather->setSource($source);

    return $pather->applyPath($sPath, $sMode, $aArguments);
  }

  public function reflectApplyDefault(schema\parser\container $source, $sPath, array $aPath, $sMode, $bRead, array $aArguments = array()) {

    list($sNamespace, $sName) = $this->parseName($sPath, $source);

    $element = $source->getElement($sName, $sNamespace);

    if ($element) {

      if ($aPath) {

        $result = $this->parsePathToken($element, $aPath, $sMode, $bRead, $aArguments);
      }
      else {

        if ($bRead) {

          $result = $element->reflectRead($aArguments);
        }
        else {

          $result = $element->reflectApply($sMode, $aArguments);
        }
      }
    }
    else {

      $result = null;
    }

    return $result;
  }
}

