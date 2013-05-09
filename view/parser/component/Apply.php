<?php

namespace sylma\view\parser\component;
use sylma\core, sylma\dom, sylma\template\parser, sylma\parser\languages\common;

class Apply extends parser\component\Apply implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
  }

  public function asArray() {

    $sValue = $this->readx('@select');
    $result = $this->getTemplate()->applyPath($sValue, $this->readx('@mode'));
    //$result = $this->getTree()->reflectApply($sValue, $this->readx('@mode'));

    $this->startLog("Apply ({$sValue})");
    $aResult = $this->getWindow()->parseArrayables(array($result));
    $this->stopLog();
//dsp($result);
    return $aResult;
  }
}

