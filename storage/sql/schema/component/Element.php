<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\schema, sylma\parser\languages\common;

class Element extends schema\xsd\component\Element implements common\stringable {

  //abstract protected function useAlias();

  protected $bAlias = false;
  protected $bRoot = false;

  public function isRoot($bValue = null) {

    if (is_bool($bValue)) $this->bRoot = $bValue;

    return $this->bRoot;
  }

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

        $mResult = $this->getAliasForm();
        break;

      case 'key' :

        $mResult = $this->getName();
        break;

      case '' :

        if ($this->useAlias()) {

          $parent = $this->getParent();
          $sParent = $parent->useAlias() ? $parent->getAlias() : $parent->getName();
          $mResult = $sParent . '_' . $this->getName();
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

  protected function getAliasForm() {

    if ($this->isSub()) {

      $table = $this->getParent();
      $foreign = $this->getParent()->getParent();

      if ($table->isMultiple()) {

        if (!$iKey = $this->getParentKey()) {

          $iKey = '0';
        }

        $aResult = array($foreign->getName(), '[', $iKey, ']', );
      }
      else {

        $aResult = array($foreign->getName());
      }

      $aResult[] = "[{$this->getName()}]";
      $mResult = $this->getWindow()->toString($aResult);
    }
    else {

      $mResult = $this->getAlias();
    }

    return $mResult;
  }

  protected function isSub() {

    return $this->getParent(false) && $this->getParent()->isSub();
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

      $sResult = $this->getName();
    }

    return $sResult;
  }

  public function asString() {

    return $this->getParent()->asString() . '.`' . $this->getName() . "`";
  }

  /**
   * @return \sylma\storage\sql\schema\Handler
   */
  protected function getParser() {

    return parent::getParser();
  }

  /**
   * @return \sylma\storage\sql\view\Resource
   */
  protected function getRoot() {

    return parent::getRoot();
  }

  protected function loadOptional() {

    $sDefault = $this->readx('@default');
    $this->isOptional(!is_null($sDefault) && $sDefault !== '');
  }

  public function getDefault() {

    $sResult = $this->readx('@default');

    if ($sResult === '') {

      if ($this->getParent(false)) {

        $sResult = $this->getParent()->getDefault();
      }
    }

    return $sResult;
  }
}

