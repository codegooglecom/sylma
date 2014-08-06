<?php

namespace sylma\template\parser\template;
use sylma\core, sylma\template as template_ns;

class Pathed extends Domed implements template_ns\parser\template {

  protected $pather;

  /**
   * @return template_ns\parser\Pather
   */
  public function getPather() {

    //if (!$this->pather) {

      $pather = $this->loadSimpleComponent('pather');

      $pather->setSource($this->getTree());
      $pather->setTemplate($this);
    //}

    return $pather;
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
   * @usedby template_ns\parser\Pather::parseExpression()
   */
  public function parseValue($sValue) {

    preg_match_all('/{([^}]+)}/', $sValue, $aMatches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

    if ($aMatches) {

      $mResult = array();
      $iOffset = 0;

      foreach ($aMatches as $i => $aResult) {

        $iStart = $aResult[0][1];

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

  public function reflectApplyFunction($sName, $sArguments = '') {

    switch ($sName) {

      case 'directory' : $result = $this->reflectDirectory($sArguments); break;
      case 'gen' : $result = $this->reflectFunctionGen($sArguments); break;

      default :

        $this->launchException("Unknown function : $sName");
    }

    return $result;
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
