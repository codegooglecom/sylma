<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\storage\sql, sylma\template;

class Field extends sql\schema\component\Field implements template\parser\tree {

  protected $parent;
  protected $query;
  protected $var;

  public function getQuery() {

    return $this->getParent()->getQuery();
  }

  public function getVar() {

    return $this->getParent()->getVar();
  }

  public function reflectApplyPath(array $aPath) {

    if (!$aPath) {

      $result = $this->reflectApplySimple();
    }
    else {

      $field = $this->getElement(array_shift($aPath));
      $result = $field->reflectApplyPath($aPath);
    }

    return $result;
  }

  public function reflectApply($sPath) {

    return $this->reflectApplyPath($this->getParser()->parsePath($sPath));
  }

  protected function reflectApplySimple() {

    $window = $this->getWindow();
    $query = $this->getQuery();

    $sName = $this->getName();

    $query->setColumn($sName);
    $var = $this->getVar();

    return $window->createCall($var, 'read', 'php-string', array($sName));
  }
}

