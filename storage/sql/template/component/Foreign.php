<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\storage\sql, sylma\template;

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

  public function reflectApplyPath(array $aPath) {

    if (!$aPath) {

      $element = $this->getElementRef();

      $result = $this->applyElement($element);
    }
    else {

      $this->throwException('Not yet implemented');
    }

    return $result;
  }

  public function reflectApply($sPath) {

    return $this->reflectApplyPath($this->getParser()->parsePath($sPath));
  }

  protected function applyElement(Table $element) {

    $sName = $element->getName();

    $window = $this->getWindow();
    $sub = $element->getQuery();
    $res = $element->getVar();
    $this->query = $sub;

    $id = $this->getParent()->reflectApply('id');

    $sub->setWhere($this->getParent()->getElement('id'), '=', $id);

    if ($this->getMaxOccurs()) {

      //$call = $window->createCall($element->getVar(), 'get', 'php-string', array($sName));
      $var = $window->createVariable('item', '\\sylma\\core\\argument');

      $this->var = $var;
      $looped = $res;

      $loop = $window->createLoop($looped, $var);

      $name = $element->reflectApply('name');
      //$call = $window->createCall($var, 'read', 'php-string', array('name'));

      $window->setScope($loop);
      $loop->addContent($this->getView()->addToResult($var, false));
      $window->stopScope();

      $result = array($loop);
    }
    else {

      $query = $this->getParent()->getQuery();

      $query->setColumn($sName);
      $var = $this->getVar();

      $result = $window->createCall($var, 'get', 'php-string', array($sName));
    }

    return $result;
  }
}

