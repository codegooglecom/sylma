<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\template\parser, sylma\parser\languages\common;

class Read extends Child implements common\arrayable, parser\component {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
  }

  protected function loadSelect($sResult) {

    if (!$sResult) {

      if ($sConstant = $this->readx('@use')) {

        $sResult = $this->getParser()->getConstant($sConstant);
      }
      else if ($sValue = $this->readx('@read')) {


        $sResult = $this->lookupPath($sValue);
      }
    }

    return $sResult;
  }

  public function build() {

    $sSelect = $this->readx('@select');
    $sMode = $this->readx('@mode');

    $sSelectOut = $sSelect ? ',@select=' . $sSelect : '';
    $sModeOut = $sMode ? ',@mode=' . $sMode : '';

    $this->log('Read [' . $sSelectOut . $sModeOut . ']');

    $aArguments = $this->getTemplate()->parseArguments($this->getNode()->getChildren());

    $aResult = array($this->getTemplate()->readPath($this->loadSelect($sSelect), $sMode, $aArguments));

    return $aResult;
  }

  public function asArray() {

    return $this->build();
  }
}

