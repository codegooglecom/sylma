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

  protected function buildSingle($sMode) {

    $this->getParent()->addElement($this, null, array(
      'default' => $this->getDefault(),
      'optional' => $this->isOptional(),
      'mode' => $sMode,
      'multiple' => $this->getMaxOccurs(true),
    ));
  }

  protected function loadID() {

    return $this->getParser()->getView()->getResult();
  }

  protected function buildMultiple(sql\schema\table $junction, sql\schema\foreign $source, sql\schema\foreign $target) {

    $window = $this->getWindow();
    $val = $window->createVariable('', 'php-null');
    $key = $window->createVariable('', 'php-integer');
    $loop = $window->createLoop($this->getParent()->getElementArgument($this->getName(), 'get'), $val, $key);

    $junction->init($key, $this->getParent()->getHandler());
    $junction->addElement($source, $this->loadID());
    $junction->addElement($target, $val);

    $loop->addContent($junction);

    return array($loop);
  }

  public function reflectRegister($content = null, $sReflector = '', $sMode = '') {

    if ($this->getMaxOccurs(true)) {

      list($junction, $source, $target) = $this->loadJunction();
      $this->getParent()->addTrigger($this->buildMultiple($junction, $source, $target));
    }
    else {

      $this->buildSingle($sMode);
    }
  }
}

