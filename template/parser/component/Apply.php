<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template\parser;

class Apply extends Child implements common\arrayable, parser\component {

  public function parseRoot(dom\element $el) {

    $this->setNode($el, true);
  }

  protected function buildReflector($sReflector, $sSelect, $sMode, array $aArguments) {

    $sReflector = $this->getWindow()->getAbsoluteClass($sReflector, (string) $this->getSourceDirectory());
    $tree = $this->getHandler()->createTree($sReflector);

    return $this->getParser()->applyPathTo($tree, $sSelect, $sMode, $aArguments);
  }

  protected function buildImport($sImport, $sSelect, $sMode, array $aArguments) {

    $tree = $this->getParser()->importTree($this->getSourceFile($sImport));
    return $this->getParser()->applyPathTo($tree, $sSelect, $sMode, $aArguments);
  }

  protected function buildDefault($sSelect, $sMode, $sXMode, array $aArguments) {

    $sSelectOut = $sSelect ? ',@select=' . $sSelect : '';
    $sModeOut = $sMode ? ',@mode=' . $sMode : '';
    $sXModeOut = $sXMode ? ',@xmode=' . $sXMode : '';

    $this->startLog('Apply [' . $sSelectOut . $sModeOut . $sXModeOut . ']');

    if (!$sSelect) {

      if ($sConstant = $this->readx('@use')) {

        $sSelect = $this->getParser()->getConstant($sConstant);
      }
      else if ($sValue = $this->readx('@read')) {

$this->launchException("Not ready");
        $sSelect = $this->lookupPath($sValue);
      }
    }

    $this->stopLog();

    return $this->getTemplate()->applyPath($sSelect, $sMode, $aArguments);
  }

  public function build() {

    $sSelect = $this->readx('@select');
    $sMode = $this->readx('@mode');

    if ($sXMode = $this->readx('@xmode')) {

      $this->getHandler()->startXMode($sXMode);
    }

    $aArguments = $this->getTemplate()->parseArguments($this->getNode()->getChildren());

    if ($sReflector = $this->readx('@reflector')) {

      $result = $this->buildReflector($sReflector, $sSelect, $sMode, $aArguments);
    }
    else if ($sImport = $this->readx('@import')) {

      $result = $this->buildImport($sImport, $sSelect, $sMode, $aArguments);
    }
    else {

      $result = $this->buildDefault($sSelect, $sMode, $sXMode, $aArguments);
    }

    $aResult = $this->getWindow()->parseArrayables(array($result));

    if (!$result && !$aResult && $this->readx('@required')) {

      $this->launchException('Apply require a template');
    }

    if ($sXMode) {

      $this->getHandler()->stopXMode();
    }

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

