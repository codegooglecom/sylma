<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common;

class Table extends sql\schema\component\Table implements sql\template\field {

  protected $bBuilded = false;
  protected $aElements = array();

  protected $query;
  protected $var;

  public function parseRoot(\sylma\dom\element $el) {

    parent::parseRoot($el);

    $select = $this->loadSimpleComponent('template/select', $this);
    $select->setTable($this->getName());

    $this->setQuery($select);
  }

  protected function build() {

    if (!$this->bBuilded) {

      $window = $this->getWindow();

      $manager = $window->addControler('mysql');
      $var = $window->addVar($window->createCall($manager, 'get', '\\sylma\\core\\argument', array($this->getQuery())));

      $this->setVar($var);

      $this->bBuilded = true;
    }
  }

  public function getQuery() {

    return $this->query;
  }

  protected function setQuery(sql\query\parser\Select $query) {

    $this->query = $query;
  }

  public function getVar() {

    $this->build();

    return $this->var;
  }

  protected function setVar(common\_object $var) {

    $this->var = $var;
  }

  public function reflectApplyPath(array $aPath) {

    if (!$aPath) {

      $this->throwException('Not yet implemented');
    }
    else if (count($aPath) == 1) {

      $field = $this->getElement(array_shift($aPath));
      $result = $field->reflectApplyPath($aPath);
    }
    else {

      $this->throwException('Not yet implemented');
    }

    return $result;
  }

  public function reflectApply($sPath) {

    $this->build();
    $aPath = $this->getParser()->parsePath($sPath);

    return $this->reflectApplyPath($aPath);
  }
}

