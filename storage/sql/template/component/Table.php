<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common;

class Table extends sql\schema\component\Table implements sql\template\field {

  protected $bBuilded = false;
  protected $aElements = array();

  protected $query;

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

      $this->throwException('Not yet implemented');
    }
    else if (count($aPath) == 1) {

      $parser = $this->getParser();
      list($sNamespace, $sName) = $parser->parseName(array_shift($aPath), $this, $this->getNode());

      $field = $this->getElement($sName, $sNamespace);
      $result = $field->reflectApplyPath($aPath, $sMode);
    }
    else {

      $this->throwException('Not yet implemented');
    }

    return $result;
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

  public function reflectApply($sPath, $sMode = '') {

    $this->build();

    if (!$sPath) {

      if (!$template = $this->lookupTemplate($sMode)) {

        $this->launchException('Cannot apply directly table without template', array(), array($this->getNode()));
      }

      $template->setTree($this);

      return $template;
    }
    else {

      $aPath = $this->getParser()->parsePath($sPath);

      return $this->reflectApplyPath($aPath, $sMode);
    }

  }
}

