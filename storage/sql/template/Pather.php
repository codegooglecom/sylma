<?php

namespace sylma\storage\sql\template;
use sylma\core, sylma\dom, sylma\storage\sql, sylma\schema, sylma\template;

class Pather extends template\parser\Pather {

  protected function parsePathTokenValue($sPath, array $aPath, $sMode, $bRead, array $aArguments = array()) {

    if ($aMatch = $this->matchContext($sPath)) {

      $aResult = $this->parsePathContext($aMatch, $aPath, $sMode);
    }
    else {

      $aResult = parent::parsePathTokenValue($sPath, $aPath, $sMode, $bRead, $aArguments);
    }

    return $aResult;
  }

  protected function parsePathDefault($sPath, array $aPath, $sMode, $bRead, array $aArguments = array()) {

    return $this->getSource()->reflectApplyDefault($sPath, $aPath, $sMode, $bRead, $aArguments);

    //return $this->parsePathElement(, $sPath, $aPath, $sMode);
  }

  protected function matchContext($sVal) {

    preg_match('/^#(\w+)$/', $sVal, $aResult);

    return $aResult;
  }

  protected function parsePathAll($sVal, $sMode, array $aArguments = array()) {

    if (trim($sVal) !== '*') {

      preg_match('/^\*\s*\^\s*([\w:-\s_,]+)$/', $sVal, $aMatch);

      if (!$aMatch) {

        $this->launchException('No valid path for exclusion', get_defined_vars());
      }

      $aResult = $this->getSource()->reflectApplyAllExcluding(array_map('trim', explode(',', $aMatch[1])), $sMode, $aArguments);
    }
    else {

      $aResult = $this->getSource()->reflectApplyAll($sMode, $aArguments);
    }

    return $aResult;
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

  protected function parseArgumentDefault($sValue, $sMode, $bRead, $bApply) {

    return $this->getSource()->getElement($sValue);
  }

  protected function parsePathFunction(array $aMatch, array $aPath, $sMode, $bRead, array $aArguments = array()) {

    if (!$this->getSource()) {

      $this->launchException('No source defined');
    }

    //$aArguments = isset($aMatch[2]) && ($aMatch[2] !== '') ? $this->parseArguments($aMatch[2], $sMode, $bRead) : array();
    $sArguments = isset($aMatch[2]) ? $aMatch[2] : '';

    return $this->getSource()->reflectApplyFunction($aMatch[1], $aPath, $sMode, $bRead, $sArguments, $aArguments);
  }
}

