<?php

namespace sylma\template\parser\template;
use sylma\core, sylma\dom, sylma\template\parser\component;

class Argumented extends Pathed {

  protected $aParameters = array();
  protected $aVariables = array();

  public function parseArguments(dom\collection $children) {

    $aResult = array();

    foreach ($children as $child) {

      if ($sKey = $this->parseElementKey($child)) {

        $aResult[$sKey] = $this->parseElement($child);
      }
      else {

        $aResult[] = $this->parseElement($child);
      }
    }

    return $aResult;
  }

  protected function setParameter($sName, component\Argument $arg) {

    $this->aParameters[$sName] = $arg;
  }

  protected function getParameters() {

    return $this->aParameters;
  }

  public function sendArguments(array $aVars) {

    $this->aSendParameters = $aVars;
  }

  protected function checkArguments() {

    $aVars = $this->aSendParameters;

    foreach ($this->getParameters() as $sName => $arg) {

      $content = isset($aVars[$sName]) ? $aVars[$sName] : $arg->getDefault();

      $var = clone $arg;

      $this->aVariables[$sName] = $var;
      $this->aHeaders[] = $var->setContent($content);
    }
  }

  public function setVariable(component\Variable $var) {

    $this->aVariables[$var->getName()] = $var;
  }

  public function getVariable($sName) {

    if (!isset($this->aVariables[$sName])) {

      $this->launchException("Variable '{$sName}' does not exists");
    }

    return $this->aVariables[$sName];
  }

  protected function addComponent(component $sub) {

    if ($sub instanceof component\Argument) {

      $this->setParameter($sub->getName(), $sub);
    }

    return parent::addComponent($sub);
  }

  protected function initRender() {

    parent::initRender();
    $this->checkArguments();
  }
}

