<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common, sylma\schema\parser;

class Foreign extends sql\schema\component\Foreign implements sql\template\field {

  protected $parent;
  protected $query;
  protected $var;

  protected function getView() {

    return $this->getParser()->getView();
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

  public function reflectApplyPath(array $aPath, $sMode) {

    if (!$aPath) {

      $element = $this->getElementRef();
      $result = $this->applyElement($element, $sMode);
    }
    else {

      $this->throwException('Not yet implemented');
    }

    return $result;
  }

  public function reflectApply($sPath, $sMode = '') {

    return $this->reflectApplyPath($this->getParser()->parsePath($sPath), $sMode);
  }

  protected function loadJunction($sName, parser\element $target) {

    $parent = $this->getParent();
    $sNamespace = $this->getNamespace();

    $result = $this->loadSimpleComponent('component/table');
    $result->setName($sName);
    $result->loadNamespace($sNamespace);

    $type = $this->loadSimpleComponent('component/complexType');
    //$type->loadNamespace($sNamespace);

    $result->setType($type);

    $particle = $this->loadSimpleComponent('component/particle');
    $type->addParticle($particle);

    $el1 = $this->loadSimpleComponent('component/field');
    $el1->setName('id_' . $parent->getName());
    $el1->setParent($result);
    $el1->loadNamespace($sNamespace);

    $el2 = $this->loadSimpleComponent('component/field');
    $el2->setName('id_' . $target->getName());
    $el2->setParent($result);
    $el2->loadNamespace($sNamespace);

    $particle->addElement($el1);
    $particle->addElement($el2);

    return array($result, $el1, $el2);
  }

  protected function applyElement(Table $element, $sMode) {

    //$sName = $element->getName();

    $window = $this->getWindow();
    $parent = $this->getParent();

    if ($this->getMaxOccurs(true)) {

      $id = $parent->getElement('id', $element->getNamespace());
      //$id->reflectRead();

      list($junction, $source, $target) = $this->loadJunction($this->getNode()->readx('@junction'), $element);

      $select1 = $this->loadSimpleComponent('template/select');
      $select1->setTable($junction);
      $select1->setWhere($source, '=', $id->reflectRead());
      $select1->isMultiple(true);

      $element->setQuery($select1);
      $select1->addJoin($element, $target, $element->getElement('id', $element->getNamespace()));
      //$select1->setTable($element);

      //$call = $window->createCall($element->getVar(), 'get', 'php-string', array($sName));
      $var = $window->createVariable('item', '\\sylma\\core\\argument');

      //$select2 = $element->getQuery();
      //$this->query = $select2;
      $this->setVar($var);

      $loop = $window->createLoop($select1->getVar(), $var);
      $val = $element->reflectApply('', $sMode);

      $window->setScope($loop);
      //$call = $window->createCall($var, 'read', 'php-string', array('name'));
      $loop->addContent($this->getView()->addToResult($val, false));
      $window->stopScope();

      $result = array($loop);
    }
    else {

      $query = $parent->getQuery();
      $element->setQuery($query);

      $id = $element->getElement('id', $element->getNamespace());
      //$id->reflectRead();

      $query->addJoin($element, $id, $this);
      $this->setVar($this->getParent()->getVar());

      //$query->setColumn($this->getName());

      //$sub->setWhere($this, '=', $id);

      $result = $element->reflectApply('', $sMode);

      /*
      $query = $this->getParent()->getQuery();

      $query->setColumn($sName);
      $var = $this->getVar();

      $result = $window->createCall($var, 'get', 'php-string', array($sName));
       *
       */
    }

    return $result;
  }
}

