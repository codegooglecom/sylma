<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common;

class Foreign extends sql\template\component\Foreign {

  const JUNCTION_MODE = 'insert';

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

  protected function buildMultiple(sql\schema\table $junction, sql\schema\foreign $source, sql\schema\foreign $target) {

    $window = $this->getWindow();
    $val = $window->createVariable('', 'php-null');
    $loop = $window->createLoop($this->getParent()->getElementArgument($this->getName(), 'get'), $val);

    $junction->addElement($source, '', $this->loadID());
    $junction->addElement($target, '', $val);

    $loop->addContent($junction);

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

