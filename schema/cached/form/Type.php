<?php

namespace sylma\schema\cached\form;
use sylma\core;

abstract class Type extends core\module\Domed {

  const MODE = 'default';

  const MODE_NULL = 'null';
  const MODE_EMPTY = 'empty';
  const MODE_DEFAULT = 'default';

  protected $sValue;
  protected $handler;
  protected $bUsed = true;
  protected $sMode = null;

  public function __construct($sValue, array $aSettings = array()) {

    $this->setValue($sValue);
    $this->setSettings($aSettings);
  }

  public function setHandler(Form $handler) {

    $this->handler = $handler;
  }

  protected function getHandler() {

    return $this->handler;
  }

  protected function addMessage($sMessage, array $aArguments = array()) {

    $this->getHandler()->addMessage($sMessage, $aArguments);
  }

  public function isUsed($bVal = null) {

    if (is_bool($bVal)) $this->bUsed = $bVal;

    return $this->bUsed;
  }

  public function setValue($sValue = '', $bValidate = false) {

    $this->sValue = $sValue;
    if ($bValidate) $this->validate();
  }

  public function getValue() {

    return $this->sValue;
  }

  protected function getMode() {

    if (is_null($this->sMode)) {

      if (!$sMode = $this->read('mode', false)) {

        $sMode = self::MODE;
      }

      $this->sMode = $sMode;
    }

    return $this->sMode;
  }

  protected function getDefault() {

    return $this->read('default', false);
  }

  protected function isOptional() {

    return $this->read('optional', false);
  }

  public function validate() {

    $bResult = false;

    if ($this->getValue()) {

      if (!$bResult = $this->validateFormat()) {

        $this->addMessage($this->translate("The field '%s' is not valid", $this->read('title')), $this->asAlias());
      }
    }
    else {

      $bResult = $this->validateEmpty();
    }

    return $bResult;
  }

  protected function validateEmpty() {

    $bResult = false;

    if ($this->getMode() === self::MODE_NULL) {

      $this->isUsed(false);
      $bResult = true;
    }
    else if ($this->isOptional()) {

      $bResult = true;
    }
    else {

      if ($this->getMode() === self::MODE_EMPTY) {

        $bResult = true;
      }
      else {

        $this->addMessage($this->translate("The field '%s' must be filled", $this->read('title')), $this->asAlias());
      }
    }

    return $bResult;
  }

  abstract protected function validateFormat();

  public function getAlias($bFull = true) {

    if ($sName = $this->getHandler()->getName() and $bFull) {

      $sName .= '['.$this->getHandler()->getKey().']';
    }

    return $sName . $this->read('alias');
  }

  public function asAlias() {

    return array('alias' => $this->getAlias());
  }

  public function escape() {

    $val = $this->getValue();

    if ($val) {

      $sResult = "'".addslashes($val)."'";
    }
    else {

      $sMode = $this->getMode();

      switch ($sMode) {

        case self::MODE_DEFAULT :

          $sResult = $this->getDefault();
          break;

        case self::MODE_NULL :
        case self::MODE_EMPTY :

          $sResult = "null";
          break;

        default :

          $this->launchException("Unknown input mode : $sMode");
      }
    }

    return $sResult;
  }

  protected function launchException($sMessage, array $aVars = array(), array $mSender = array()) {

    $aVars[] = $this->getSettings();

    parent::launchException($sMessage, $aVars, $mSender);
  }
}

