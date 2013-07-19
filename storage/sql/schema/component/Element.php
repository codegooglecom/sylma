<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\schema, sylma\parser\languages\common;

class Element extends schema\xsd\component\Element implements common\stringable {

  //abstract protected function useAlias();

  protected $bAlias = false;

  protected function getParentKey() {

    return '';
  }

  protected function reflectFunctionAlias($sMode, $bRead, $sArguments) {

    $aArguments = $this->getParser()->getPather()->parseArguments($sArguments, $sMode, $bRead);
    $sMode = $aArguments ? array_pop($aArguments) : '';

    return $this->getAlias($sMode);
  }

  public function getAlias($sMode = '') {

    switch ($sMode) {

      case 'form' :

        if ($this->isSub()) {

          $mResult = $this->getWindow()->toString(array($this->getParent()->getParent()->getName(), '[', $this->getParentKey(), "][{$this->getName()}]"));
        }
        else {

          $mResult = $this->getAlias();
        }

        break;

      case 'key' :

        $mResult = $this->getName();
        break;

      case '' :

        if ($this->useAlias()) {

          $mResult = $this->getParent()->getName() . '_' . $this->getName();
        }
        else {

          $mResult = $this->getName();
        }

        break;

      default :

        $this->launchException("Unknown alias() mode : $sMode");
    }

    return $mResult;
  }

  protected function isSub() {

    return $this->getParent()->isSub();
  }

  public function useAlias($bVal = null) {

    if (is_bool($bVal)) $this->bAlias = $bVal;

    return $this->bAlias;
  }

  public function asAlias() {

    return $this->asString() . ($this->useAlias() ? ' AS `' . $this->getAlias() . '`' : '');
  }

  public function getTitle() {

    if (!$this->getNode(false) or !$sResult = $this->readx('@title')) {

      $sResult = $this->getAlias();
    }

    return $sResult;
  }

  public function asString() {

    return $this->getParent()->asString() . '.`' . $this->getName() . "`";
  }

  /**
   * 
   * @return \sylma\storage\sql\schema\Handler
   */
  protected function getParser() {
    
    return parent::getParser();
  }
  
  protected function loadOptional() {

    $sDefault = $this->readx('@default');
    $this->isOptional(!is_null($sDefault) && $sDefault !== '');
  }

  public function getDefault() {

    return $this->readx('@default');
  }
}

