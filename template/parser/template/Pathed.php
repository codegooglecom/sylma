<?php

namespace sylma\template\parser\template;
use sylma\core, sylma\template;

abstract class Pathed extends Domed {

  protected $pather;

  /**
   * @return template\parser\Pather
   */
  public function getPather() {

    //if (!$this->pather) {

      $result = $this->buildPather($this->getTree());
    //}

    return $result;
  }

  protected function buildPather(template\parser\tree $tree) {

    $result = $this->loadSimpleComponent('pather');

    $result->setSource($tree);
    $result->setTemplate($this);

    return $result;
  }

  public function readPath($sPath, $sMode, array $aArguments = array()) {

    $pather = $this->getPather();

    return $pather->readPath($sPath, $sMode, $aArguments);
  }

  public function applyPath($sPath, $sMode, array $aArguments = array()) {

    $pather = $this->getPather();

    return $pather->applyPath($sPath, $sMode, $aArguments);
  }

  /**
   * @usedby template\parser\Pather::parseExpression()
   */
  public function parseValue($sValue) {

    preg_match_all('/{([^}]+)}/', $sValue, $aMatches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

    if ($aMatches) {

      $mResult = array();
      $iOffset = 0;

      foreach ($aMatches as $i => $aResult) {

        $iStart = mb_strlen(substr($sValue, 0, $aResult[0][1]), 'utf-8');

        $iVarLength = mb_strlen($aResult[0][0]);
        $val = $this->applyPath($aResult[1][0], '');

        $iDiff = $iStart - $iOffset;

        $sStart = mb_substr($sValue, $iOffset, $iDiff);

        if ($i == (count($aMatches) - 1)) {

          $mResult[] = array($sStart, $val, mb_substr($sValue, $iStart + $iVarLength));
        }
        else {

          $mResult[] = array($sStart, $val);
          $iOffset += $iDiff + $iVarLength;
        }
      }
    }
    else {

      $mResult = $sValue;
    }

    return $mResult;
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    switch ($sName) {

      case 'directory' : $result = $this->reflectDirectory($sArguments); break;
      case 'gen' : $result = $this->reflectFunctionGen($sArguments); break;
      case 'root' : $result = $this->reflectFunctionRoot($aPath, $sMode, $bRead, $aArguments); break;

      default :

        $this->launchException("Unknown function : $sName");
    }

    return $result;
  }

  protected function reflectFunctionRoot(array $aPath, $sMode, $bRead = false, array $aArguments = array()) {

    $pather = $this->buildPather($this->getHandler()->getResource()->getTree());

    return $pather->parsePathToken($aPath, $sMode, $bRead, $aArguments);
  }

  protected function reflectFunctionGen($sArguments) {

    $aArguments = $this->getPather()->parseArguments($sArguments);

    if (count($aArguments) > 1) {

      $this->launchException('Too much arguments for gen()');
    }

    //return uniqid(current($aArguments));
    return $this->getWindow()->callFunction('uniqid', 'php-string', $aArguments)  ;
  }

  protected function reflectDirectory($sArguments) {

    $sPath = $sArguments;

    return (string) $this->getSourceDirectory($sPath);
  }

}

