<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common;

class Foreign extends sql\template\component\Foreign {

  protected function reflectFunctionAll(array $aPath, $sMode, array $aArguments = array()) {

    return null;
  }

  protected function reflectFunctionRef(array $aPath, $sMode, array $aArguments = array()) {

    return null;
  }

  protected function buildSingle() {

    $this->getParent()->addElement($this, $this->getDefault());
  }

  protected function loadID() {

    return $this->getParser()->getView()->getResult();
  }

  protected function buildMultiple(sql\schema\table $junction, sql\schema\field $source, sql\schema\field $target) {

    $window = $this->getWindow();
    $val = $window->createVariable('', 'php-null');
    $loop = $window->createLoop($this->getParent()->getElementArgument($this->getName()), $val);

    $query = $this->loadSimpleComponent('template/insert');

    $junction->setQuery($query);
    $query->setTable($junction);
    $junction->addElement($source, '', $this->loadID());
    $junction->addElement($target, '', $this->reflectEscape($val));

    $loop->addContent($query);

    return array($loop);
  }

  public function reflectRegister() {

    if ($this->getMaxOccurs(true)) {

      list($junction, $source, $target) = $this->loadJunction();
      $this->getParent()->addTrigger($this->buildMultiple($junction, $source, $target));
    }
    else {

      $this->buildSingle();
    }
  }
}

