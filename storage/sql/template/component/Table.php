<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\storage\sql, sylma\schema\parser;

class Table extends sql\schema\component\Table implements sql\template\field {

  protected $bBuilded = false;
  protected $aElements = array();

  protected $query;

  public function setParent(parser\element $parent) {

    $this->parent = $parent;
  }

  public function getParent($bDebug = true) {

    if (!$this->parent && $bDebug) {

      $this->throwException('No parent');
    }

    return $this->parent;
  }

  public function parseRoot(\sylma\dom\element $el) {

    parent::parseRoot($el);
  }

  protected function build() {

    if (!$this->bBuilded) {

      //$this->getQuery()->addTo($this->getWindow());

      $this->bBuilded = true;
    }
  }

  public function getQuery() {

    if (!$this->query) {

      $select = $this->loadSimpleComponent('template/select', $this);
      $select->setTable($this);
      $select->isMultiple(false);

      $this->setQuery($select);
    }

    return $this->query;
  }

  public function setQuery(sql\query\parser\Select $query) {

    $this->query = $query;
  }

  public function getVar() {

    $this->build();

    return $this->getParent(false) ? $this->getParent()->getVar() : $this->getQuery()->getVar();
  }

  public function reflectApplyPath(array $aPath, $sMode) {

    if (!$aPath) {

      $this->launchException('Table must not be applied (internally) without path neither template, reflectApply() should be called instead');
    }

    return $this->parsePathToken($aPath, $sMode);
  }

  protected function parsePathToken($aPath, $sMode) {

    return $this->getParser()->parsePathToken($this, $aPath, $sMode);
  }

  protected function lookupTemplate($sMode) {

    if ($template = $this->getParser()->lookupTemplate($this, 'element', $sMode)) {

      $result = clone $template;
    }
    else {

      $result = null;
    }

    return $result;
  }

  protected function parsePath($sPath) {

    $aResult = $this->getParser()->parsePath($sPath);

    return $aResult;
  }

  public function reflectApply($sPath, $sMode = '*') {

    $this->build();

    if (!$sPath) {

      if (!$template = $this->lookupTemplate($sMode)) {

        $this->launchException('Cannot apply table without template', array($this->getNode()));
      }

      $template->setTree($this);

      $result = $template;
    }
    else {

      $result = $this->reflectApplyPath($this->parsePath($sPath), $sMode);
    }

    return $result;
  }
}

