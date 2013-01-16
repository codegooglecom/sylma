<?php

namespace sylma\parser\action\compiler;
use sylma\core, sylma\dom, sylma\storage\fs, sylma\parser\languages\common, sylma\parser\languages\php, sylma\parser;

abstract class Variabled extends Caller {

  protected $aVariables = array();

  protected function parseElementSelf(dom\element $el) {

    if ($el->getName() == 'get-variable') $mResult = $this->reflectGetVariable($el);
    else $mResult = parent::parseElementSelf($el);

    return $mResult;
  }

  protected function getVariable($sName) {

    if (!array_key_exists($sName, $this->aVariables)) {

      $this->throwException(sprintf('Variable %s does not exists', $sName));
    }

    return $this->aVariables[$sName];
  }

  protected function setVariable(dom\element $el, $obj) {

    $result = null;

    if ($sName = $el->readAttribute('set-variable', $this->getNamespace(), false)) {

      if (array_key_exists($sName, $this->aVariables)) {

        $result = $this->aVariables[$sName];

        if ($obj instanceof common\_var) {

          $obj->insert();
        }

        $result->insert($obj);
      }
      else {

        if ($obj instanceof common\_var) {

          $result = $obj;
          $obj->insert();
        }
        else if ($obj instanceof php\basic\Called) {

          $result = $obj->getVar();
        }
        else {

          $result = $this->getWindow()->addVar($obj);
        }

        $this->aVariables[$sName] = $result;
      }
    }

    return $result;
  }

  protected function reflectGetVariable(dom\element $el) {

    $sName = $el->readAttribute('name');

    if (!array_key_exists($sName, $this->aVariables)) {

      $this->throwException(sprintf('Unknown variable : %s', $sName));
    }

    $var = $this->getVariable($sName);

    //if ($var instanceof php\basic\Called) $var = $var->getVar(false);

    $aResult = array();
    $children = $el->getChildren();

    $aResult = array_merge($aResult, $this->runConditions($var, $children));
    $aResult = array_merge($aResult, $this->runVar($var, $children));

    if (!$aResult) $aResult[] = $var;

    return count($aResult) == 1 ? reset($aResult) : $aResult;
  }

}
