<?php

namespace sylma\storage\sql\template;
use sylma\core, sylma\dom, sylma\storage\sql, sylma\schema, sylma\template;

class Pather extends template\parser\Pather {

  protected function parsePathTokenValue($sPath, array $aPath, $sMode) {

    if ($aMatch = $this->matchContext($sPath)) {

      $aResult = $this->parsePathContext($aMatch, $aPath, $sMode);
    }
    else {

      $aResult = parent::parsePathTokenValue($sPath, $aPath, $sMode);
    }

    return $aResult;
  }

  protected function parsePathDefault($sPath, array $aPath, $sMode) {

    return $this->getSource()->reflectApplyDefault($sPath, $aPath, $sMode);

    //return $this->parsePathElement(, $sPath, $aPath, $sMode);
  }

  protected function matchContext($sVal) {

    preg_match('/^#(\w+)$/', $sVal, $aResult);

    return $aResult;
  }

  protected function parsePathAll(array $aPath, $sMode) {

    return $this->getSource()->reflectApplyAll($aPath, $sMode);
  }
/*
  protected function parsePathElement(schema\parser\element $source, $sPath, array $aPath, $sMode) {


    list($sNamespace, $sName) = $source->parseName($sPath);

    $element = $source->getElement($sName, $sNamespace);

    return $element ? $element->reflectApplyPath($aPath, $sMode) : null;
  }
*/
  protected function parsePathContext(array $aMatch, array $aPath, $sMode) {

    $source = $this->getSource();

    switch ($aMatch[1]) {

      case 'element' :

        $result = $source->reflectApplyPath($aPath, $sMode);
        break;

      case 'type' :

        $type = $source->getType();
        $type->setElementRef($source);

        $result = $type->reflectApplyPath($aPath, $sMode);
        break;

      default :

        $this->launchException('Unknown context', get_defined_vars());
    }

    return $result;
  }

  protected function parsePathFunction(array $aMatch, array $aPath, $sMode) {

    if (!$this->getSource()) {

      $this->launchException('No source defined');
    }

    return $this->getSource()->reflectApplyFunction($aMatch[1], $aPath, $sMode);
  }
}

