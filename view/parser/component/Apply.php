<?php

namespace sylma\view\parser\component;
use sylma\core, sylma\dom, sylma\template\parser, sylma\parser\languages\common;

class Apply extends parser\component\Apply implements common\arrayable, common\addable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
  }

  public function build() {

    if (!$sValue = $this->readx('@select')) {

      if ($sConstant = $this->readx('@use')) {

        $sValue = $this->getParser()->getConstant($sConstant);
      }
    }

    $result = $this->getTemplate()->applyPath($sValue, $this->readx('@mode'));

    $this->startLog("Apply ({$sValue})");
    $aResult = $this->getWindow()->parseArrayables(array($result));
    $this->stopLog();

    return $aResult;
  }

  public function onAdd() {

    $window = $this->getWindow();
    $window->add($window->toString($this->build()));
  }

  public function asArray() {

    return $this->build();
  }
}

