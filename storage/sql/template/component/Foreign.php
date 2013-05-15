<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common, sylma\schema\parser;

class Foreign extends sql\schema\component\Foreign implements sql\template\pathable, parser\element {

  protected $parent;
  protected $query;
  protected $var;

  public function setParent(parser\element $parent) {

    $this->parent = $parent;
  }

  public function getParent($bDebug = true) {

    if (!$this->parent && $bDebug) {

      $this->throwException('No parent');
    }

    return $this->parent;
  }

  /**
   *
   * @return sql\query\parser\Select
   */
  public function getQuery() {

    return $this->query;
  }

  public function getVar() {

    return $this->var;
  }

  protected function setVar(common\_var $var) {

    $this->var = $var;
  }

  public function reflectApplyDefault($sPath, array $aPath, $sMode) {

    return $this->getParser()->reflectApplyDefault($this, $sPath, $aPath, $sMode);
  }

  public function reflectApplyPath(array $aPath, $sMode) {

    if (!$aPath) {

      $result = $this->reflectApplySelf($sMode);
    }
    else {

      $result = $this->getParser()->parsePathToken($this, $aPath, $sMode);
    }

    return $result;
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode) {

    switch ($sName) {

      case 'all' : $result = $this->reflectFunctionAll($aPath, $sMode); break;
      case 'ref' : $result = $this->reflectFunctionRef($aPath, $sMode); break;

      default :

        $this->launchException("Invalid function name : '{$sName}'");
        //$result = $this->getParser()->parsePathFunction($this, $aMatch, $aPath, $sMode);
    }

    return $result;
  }

  protected function reflectFunctionRef(array $aPath, $sMode) {

    $element = $this->getElementRef();
    $result = $this->applyElement($element, $sMode);

    return $result;
  }

  protected function reflectFunctionAll(array $aPath, $sMode) {

    if ($aPath) {

      $this->throwException('Not yet implemented');
    }

    $element = $this->getElementRef();

    $collection = $this->loadSimpleComponent('component/collection');

    $collection->setQuery($element->getQuery());
    $collection->setTable($element);

    return $collection->reflectApplyAll(array('*'), $sMode);
  }

  public function reflectApply($sMode = '') {
$this->launchException('Not used ?');
    return $this->getParser()->parsePath($this, $sPath, $sMode);
  }

  protected function lookupTemplate($sMode) {

    return $this->getParser()->lookupTemplate($this, 'element', $sMode);
  }

  protected function reflectApplySelf($sMode) {

    if ($result = $this->lookupTemplate($sMode)) {

      $result->setTree($this);
    }
    else {

      $this->launchException('No template found', get_defined_vars());
      //$result = $this->reflectRead();
    }

    return $result;
  }

  protected function loadJunction($sName, parser\element $target) {

    $parent = $this->getParent();
    $sNamespace = $this->getNamespace();

    $table = $this->loadSimpleComponent('component/table');
    $table->setName($sName);
    $table->loadNamespace($sNamespace);

    $type = $this->loadSimpleComponent('component/complexType');
    //$type->loadNamespace($sNamespace);

    $table->setType($type);

    $particle = $this->loadSimpleComponent('component/particle');
    $type->addParticle($particle);

    $el1 = $this->loadSimpleComponent('component/field');
    $el1->setName('id_' . $parent->getName());
    $el1->setParent($table);
    $el1->loadNamespace($sNamespace);

    $el2 = $this->loadSimpleComponent('component/field');
    $el2->setName('id_' . $target->getName());
    $el2->setParent($table);
    $el2->loadNamespace($sNamespace);

    $particle->addElement($el1);
    $particle->addElement($el2);

    return array($table, $el1, $el2);
  }

  protected function applyElement(Table $element, $sMode) {

    //$sName = $element->getName();

    $parent = $this->getParent();

    if ($this->getMaxOccurs(true)) {

      $id = $parent->getElement('id', $element->getNamespace());

      list($table, $source, $target) = $this->loadJunction($this->getNode()->readx('@junction'), $element);

      $query = $table->getQuery();
      $collection = $this->getParser()->createCollection();

      $collection->setQuery($table->getQuery());
      $collection->setTable($element);

      $query->setWhere($source, '=', $id->reflectRead());

      $element->setQuery($query);
      $query->addJoin($element, $target, $element->getElement('id', $element->getNamespace()));

      $result = $collection->reflectApplyAll(array(), $sMode);
      //$result = $this->getParser()->parsePath($collection, '*', $sMode);
    }
    else {

      $query = $parent->getQuery();
      $element->setQuery($query);

      $id = $element->getElement('id', $element->getNamespace());

      $query->addJoin($element, $id, $this);

      $result = $element->reflectApply($sMode);
    }

    return $result;
  }
}

