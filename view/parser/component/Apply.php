<?php

namespace sylma\view\parser\component;
use sylma\core, sylma\dom, sylma\template\parser, sylma\parser\languages\common;

class Apply extends parser\component\Apply implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el, true);
  }

  public function build() {

    $sSelect = $this->readx('@select');
    $sMode = $this->readx('@mode');

    if ($sImport = $this->readx('@import')) {

      $tree = $this->getParser()->importTree($this->getSourceFile($sImport));
      $result = $this->getParser()->applyPathTo($tree, $sSelect, $sMode);
    }
    else {

      $this->startLog("Apply [@select={$sSelect},@mode={$sMode}]");

      if (!$sSelect) {

        if ($sConstant = $this->readx('@use')) {

          $sSelect = $this->getParser()->getConstant($sConstant);
        }
        else if ($sValue = $this->readx('@read')) {
$this->launchException("Not ready");
          $sSelect = $this->lookupPath($sValue);
        }
      }

      $aArguments = $this->getTemplate()->parseArguments($this->getNode()->getChildren());

      $result = $this->getTemplate()->applyPath($sSelect, $sMode, $aArguments);
    }

    $aResult = $this->getWindow()->parseArrayables(array($result));

    if (!$aResult && $this->readx('@required')) {

      $this->launchException('Apply require a template');
    }

    $this->stopLog();

    return $aResult;
  }

  protected function lookupPath($sValue) {

    $sResult = $this->getTemplate()->readPath($sValue, '');

    if (!is_string($sResult)) {

      $this->launchException("String expected as read path in '$sValue'");
    }

    return $sResult;
  }

  public function asArray() {

    return $this->build();
  }
}

