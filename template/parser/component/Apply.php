<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom;

class Apply extends Read {

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
    $iArguments = count($aArguments);
    $sArguments = $iArguments ? "($iArguments)" : '';

    $sElement = $this->getNode()->asToken();
    $this->startLog('Apply [' . $sSelectOut . $sModeOut . $sXModeOut . "]$sArguments", array('element' => $sElement));

    $this->stopLog();

    try {

      $result = $this->getTemplate()->applyPath($this->loadSelect($sSelect), $sMode, $aArguments);

    } catch (core\exception $e) {

      $e->addPath($this->getNode()->asToken());
      throw $e;
    }

    return $result;
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

    $aResult = $this->getWindow()->parse($result, true);

    if (\Sylma::read('template/required') && !$result && !$aResult && $this->readx('@required')) {

      $this->launchException('Apply require a template');
    }

    if ($sXMode) {

      $this->getHandler()->stopXMode();
    }

    return $aResult;
  }
}

