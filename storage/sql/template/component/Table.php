<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\storage\sql, sylma\schema\parser, sylma\parser\languages\common;

class Table extends sql\schema\component\Table implements sql\template\field {

  protected $bBuilded = false;
  protected $aElements = array();

  protected $query;
  protected $var;
  protected $loop;
  protected $source;

  protected $bRoot = false;

  public function parseRoot(\sylma\dom\element $el) {

    parent::parseRoot($el);
  }

  public function isRoot($bValue = null) {

    if (is_bool($bValue)) $this->bRoot = $bValue;

    return $this->bRoot;
  }

  public function setParent(parser\element $parent) {

    $this->parent = $parent;
  }

  public function getParent($bDebug = true) {

    if (!$this->parent && $bDebug) {

      $this->throwException('No parent');
    }

    return $this->parent;
  }

  protected function preBuild() {

    //if (!$this->bBuilded) {

      if ($this->getQuery()->isMultiple()) {

        $window = $this->getWindow();

        $var = $window->createVariable('item', '\\sylma\\core\\argument');
        $this->setSource($var);

        $this->loop = $window->createLoop($this->getQuery()->getVar(), $var);
      }

      //$this->bBuilded = true;
    //}
  }

  public function getSource() {

    return $this->source ? $this->source : $this->getQuery()->getVar();
  }

  protected function setSource($source) {

    $this->source = $source;
  }

  protected function postBuild($result) {

    if ($loop = $this->loop) {

      $window = $this->getWindow();
      $window->setScope($loop);

      $loop->addContent($this->getParser()->getView()->addToResult($result, false));
      $window->stopScope();

      $result = $loop;
    }

    return $result;
  }

  public function getQuery() {

    if (!$this->query) {

      $this->setQuery($this->createQuery('select'));
    }

    return $this->query;
  }

  protected function createQuery($sName) {

    $query = $this->loadSimpleComponent("template/$sName", $this);
    $query->setTable($this);

    return $query;
  }

  public function setQuery(sql\query\parser\Basic $query) {

    $this->query = $query;
  }

  protected function setVar(common\_var $var) {

    $this->var = $var;
  }

  public function reflectApplyPath(array $aPath, $sMode) {

    if (!$aPath) {

      $this->launchException('Table must not be applied (internally) without path neither template, reflectApply() should be called instead');
    }

    return $this->parsePathTokens($aPath, $sMode);
  }

  protected function parsePathTokens($aPath, $sMode) {

    return $this->getParser()->parsePathTokens($this, $aPath, $sMode);
  }

  protected function lookupTemplate($sMode) {

    return $this->getParser()->lookupTemplate($this, 'element', $sMode, $this->isRoot());
  }

  protected function parsePaths($sPath) {

    $aResult = $this->getParser()->parsePaths($sPath);

    return $aResult;
  }

  public function reflectApply($sPath, $sMode = '*') {

    if (!$sPath) {

      if (!$template = $this->lookupTemplate($sMode)) {

        $this->launchException('Cannot apply table without template', array($this->getNode()));
      }

      $this->preBuild();
      $template->setTree($this);

      $result = $this->postBuild($template);
    }
    else {

      $result = $this->reflectApplyPath($this->parsePaths($sPath), $sMode);
    }

    return $result;
  }
}

