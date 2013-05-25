<?php

namespace sylma\view\parser\component;
use sylma\core, sylma\dom, sylma\template\parser, sylma\parser\languages\common;

class Apply extends parser\component\Apply implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
  }

  public function build() {

    if (!$sValue = $this->readx('@select')) {

      if ($sConstant = $this->readx('@use')) {

        $sValue = $this->getParser()->getConstant($sConstant);
      }
    }

    $this->startLog("Apply ({$sValue})");

    $result = $this->getTemplate()->applyPath($sValue, $this->readx('@mode'));
    $aResult = $this->getWindow()->parseArrayables(array($result));

    $this->stopLog();

    return $aResult;
  }

  public function _onAdd() {

    $window = $this->getWindow();
    $window->add($window->toString($this->build()));
  }

  public function asArray() {

    return $this->build();
  }
}

