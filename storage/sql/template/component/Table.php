<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\dom, sylma\storage\sql, sylma\schema;

class Table extends Rooted implements sql\template\pathable, schema\parser\element {

  protected $bBuilded = false;
  protected $aElements = array();

  protected $loop;

  public function parseRoot(dom\element $el) {

    parent::parseRoot($el);
  }

  public function setParent(schema\parser\element $parent) {

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

  public function reflectApplyAll($sMode) {

    $aResult = array();

    foreach ($this->getElements() as $element) {

      $aResult[] = $element->reflectApply($sMode);
    }

    return $aResult;
  }

  public function reflectApplyAllExcluding(array $aExcluded, $sMode) {

    $aResult = array();
    $aRemoved = array();

    foreach ($aExcluded as $sName) {

      list($sNamespace, $sName) = $this->getParser()->parseName($sName);
      $aRemoved[] = $this->getElement($sName, $sNamespace, false);
    }

    foreach ($this->getElements() as $element) {

      foreach ($aRemoved as $excluded) {

        if ($excluded === $element) {

          continue 2;
        }
      }

      $aResult[] = $element->reflectApply($sMode);
    }

    return $aResult;
  }
}

