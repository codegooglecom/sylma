<?php

namespace sylma\storage\sql\template\view;
use sylma\core, sylma\storage\sql;

class Foreign extends sql\template\component\Foreign {

  protected function getParentKey() {

    return $this->getParent()->getKey();
  }

  protected function addToQuery() {

    $this->getParent()->addElementToQuery($this);
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    switch ($sName) {

      case 'values' : $result = $this->reflectValues(); break;
      case 'extract' : $result = $this->reflectFunctionExtract($aPath, $sMode, $aArguments, $bRead); break;
      case 'join' : $result = $this->reflectFunctionJoin($aPath, $sMode, $aArguments, $bRead); break;

      default :

        $result = parent::reflectApplyFunction($sName, $aPath, $sMode, $bRead, $sArguments, $aArguments);
    }

    return $result;
  }

  public function reflectRead() {

    if ($this->getMaxOccurs(true)) {

      $this->launchException('Cannot read multiple foreign, values() should be used');
    }

    $this->addToQuery();

    return $this->getParent()->getSource()->call('read', array($this->getAlias(), false), 'php-string');
  }

  protected function reflectValues() {

    $parent = $this->getParent();
    list($junction, $source, $target) = $this->loadJunction();

    $query = $junction->getQuery();
    $key = $parent->getElement($this->getKey(), $parent->getNamespace());

    $query->setElement($target);
    $query->setWhere($source, '=', $key->reflectRead());
    $query->setMethod('extract');

    return $query->getCall()->call('asArray');
  }

  protected function reflectFunctionRef(array $aPath, $sMode, array $aArguments = array(), $bRead = false) {

    if ($this->getParent()->isStatic()) {

      $table = $this->getElementRef();
      $table->setQuery($this->getParent()->getQuery());

      if ($aPath) {

        // @TODO : only works with default as first path
        $result = $table->reflectApplyDefault(array_shift($aPath), $aPath, $sMode, $bRead, $aArguments, true);
      }
      else {

        $result = $table->reflectApply($sMode, $aArguments, true);
      }
    }
    else {

      $result = $this->applyElement($aPath, $sMode, $aArguments, $bRead);
    }

    return $result;
  }

  protected function reflectFunctionExtract(array $aPath, $sMode, array $aArguments = array(), $bRead = false) {

    $collection = $this->buildMultiple();
    $collection->getQuery()->setMethod('extract');

    $this->getWindow()->parse($this->getHandler()->parsePathToken($collection->getTable(), $aPath, $sMode, $bRead, $aArguments));

    return $collection->getQuery()->getCall()->call('asArray');
  }

  protected function reflectFunctionCollection(array $aPath, $sMode, array $aArguments = array()) {

    if (!$this->getMaxOccurs(true)) {

      $this->launchException('No collection defined in simple foreign');
    }

    $collection = $this->buildMultiple();

    return $this->getHandler()->parsePathToken($collection, $aPath, $sMode, $aArguments);
  }

  protected function reflectFunctionJoin(array $aPath, $sMode, array $aArguments = array()) {

    if (!$this->getMaxOccurs(true)) {

      $this->launchException('Cannot join simple foreign');
    }

    $targetTable = $this->getElementRef();
    $currentTable = $this->getParent();

    $query = $currentTable->getQuery();

    $targetTable->setSource($currentTable->getSource());
    $targetTable->setQuery($query);
    $targetTable->insertQuery(false);

    list($junctionTable, $junctionCurrent, $junctionTarget) = $this->loadJunction();

    $query->addJoin($junctionTable, $junctionCurrent, $currentTable->getElement($this->getKey()));
    $query->addJoin($targetTable, $junctionTarget, $targetTable->getElement($this->getKey()));

    return $this->getHandler()->parsePathToken($targetTable, $aPath, $sMode, false, $aArguments);
  }

  protected function buildSingle() {

    $element = $this->getElementRef();

    $parent = $this->getParent();
    $query = $parent->getQuery();

    $element->setSource($parent->getSource());
    $element->setQuery($query);
    $element->insertQuery(false);

    $key = $element->getElement($this->getKey(), $element->getNamespace());

    $table = $query->addJoin($element, $key, $this);
    $this->setElementRef($table);
  }

  protected function buildMultiple() {

    $parent = $this->getParent();
    $element = $this->getElementRef();

    $key = $parent->getElement($this->getKey(), $parent->getNamespace());

    list($table, $field, $target) = $this->loadJunction();

    $query = $table->getQuery(true);
    $result = $this->getParser()->createCollection();

    $result->setTable($element);
    $result->setQuery($table->getQuery());

    $query->setWhere($field, '=', $key->reflectRead());

    $element->setQuery($query);
    $query->addJoin($element, $target, $element->getElement($this->getKey(), $element->getNamespace()));

    return $result;
  }

  protected function applyElement(array $aPath, $sMode, array $aArguments = array(), $bRead = false) {

    //$sName = $element->getName();

    if ($this->getMaxOccurs(true)) {

      $collection = $this->buildMultiple();

      if ($aPath) {

        $this->launchException('Not yet implemented');
        // reflectApplyAll() need $aPath
      }

      $result = $collection->reflectApplyAll($sMode, $aArguments);
      //$result = $this->getParser()->parsePath($collection, '*', $sMode);
    }
    else {

      $this->buildSingle();
      $table = $this->getElementRef();

      if ($aPath) {

        $result = $this->getParser()->parsePathToken($table, $aPath, $sMode, $bRead, $aArguments);
      }
      else {

        $result = $table->reflectApply($sMode, $aArguments);
      }
    }

    return $result;
  }
}

