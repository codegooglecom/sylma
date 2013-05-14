<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\dom, sylma\storage\sql, sylma\schema\parser, sylma\parser\languages\common;

class Table extends Rooted implements sql\template\pathable, parser\element {

  protected $bBuilded = false;
  protected $aElements = array();

  protected $loop;

  public function parseRoot(dom\element $el) {

    parent::parseRoot($el);
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

  public function getQuery() {

    if (!$this->query) {

      $this->setQuery($this->createQuery('select'));
    }

    return $this->query;
  }

  public function getSource() {

    return $this->source ? $this->source : $this->getQuery()->getVar();
  }

  protected function createQuery($sName) {

    $query = $this->loadSimpleComponent("template/$sName", $this);
    $query->setTable($this);

    return $query;
  }

  public function reflectApplyDefault($sPath, array $aPath, $sMode) {

    return $this->getParser()->reflectApplyDefault($this, $sPath, $aPath, $sMode);
  }

  public function reflectApply($sMode = '', $bStatic = false) {

    if ($result = $this->lookupTemplate($sMode)) {

      $result->setTree($this);
    }
    else {

      if (!$sMode) {

        $this->launchException('Cannot apply table without template and without mode');
      }

      $result = null;
    }

    return $result;
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode) {

    switch ($sName) {

      //case 'apply' : $result = $this->reflectApply(''); break;

      default :

        $this->launchException(sprintf('Uknown function "%s()"', $sName), get_defined_vars());
    }

    return $result;
  }

  public function reflectApplyAll(array $aPath, $sMode) {

    $aResult = array();

    foreach ($this->getElements() as $element) {

      $element->setParent($this);
      $aResult[] = $element->reflectApplyPath($aPath, $sMode);
    }

    return $aResult;
  }
}

