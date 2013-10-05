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
      case 'join' : $result = $this->reflectFunctionJoin($aPath, $sMode, $aArguments); break;

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

    return $this->getParent()->getSource()->call('read', array($this->getAlias()), 'php-string');
  }

  protected function reflectValues() {

    $parent = $this->getParent();
    list($junction, $source, $target) = $this->loadJunction();

    $query = $junction->getQuery();
    $id = $parent->getElement('id', $parent->getNamespace());

    $query->setElement($target);
    $query->setWhere($source, '=', $id->reflectRead());
    $query->setMethod('extract');

    return $query->getCall()->call('asArray');
  }

  protected function reflectFunctionRef(array $aPath, $sMode, array $aArguments = array()) {

    $result = $this->applyElement($aPath, $sMode, $aArguments);

    return $result;
  }

  protected function reflectFunctionJoin(array $aPath, $sMode, array $aArguments = array()) {

    $targetTable = $this->getElementRef();
    $currentTable = $this->getParent();

    $query = $currentTable->getQuery();

    $targetTable->setSource($currentTable->getSource());
    $targetTable->setQuery($query);
    $targetTable->insertQuery(false);

    list($junctionTable, $junctionCurrent, $junctionTarget) = $this->loadJunction();

    $query->addJoin($junctionTable, $junctionCurrent, $currentTable->getElement('id'));
    $query->addJoin($targetTable, $junctionTarget, $targetTable->getElement('id'));

    return $this->getParser()->parsePathToken($targetTable, $aPath, $sMode, false, $aArguments);
  }

  protected function buildSingle() {

    if (!$this->bBuilded) {

      $element = $this->getElementRef();

      $parent = $this->getParent();
      $query = $parent->getQuery();

      $element->setSource($parent->getSource());
      $element->setQuery($query);
      $element->insertQuery(false);

      $id = $element->getElement('id', $element->getNamespace());

      $query->addJoin($element, $id, $this);
      $this->bBuilded = true;
    }
  }

  protected function buildMultiple() {

    $parent = $this->getParent();
    $element = $this->getElementRef();

    $id = $parent->getElement('id', $parent->getNamespace());

    list($table, $field, $target) = $this->loadJunction();

    $query = $table->getQuery();
    $result = $this->getParser()->createCollection();

    $result->setTable($element);
    $result->setQuery($table->getQuery());

    $query->setWhere($field, '=', $id->reflectRead());

    $element->setQuery($query);
    $query->addJoin($element, $target, $element->getElement('id', $element->getNamespace()));

    return $result;
  }

  protected function applyElement(array $aPath, $sMode, array $aArguments = array()) {

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
      $element = $this->getElementRef();

      if ($aPath) {

        $result = $this->getParser()->parsePathToken($element, $aPath, $sMode);
      }
      else {

        $result = $element->reflectApply($sMode, $aArguments);
      }
    }

    return $result;
  }
}

