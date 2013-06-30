<?php

namespace sylma\schema\cached\form;
use sylma\core;

abstract class Type extends core\module\Domed {

  const EMPTY_VALUE = '#sylma-empty#';

  protected $sValue;
  protected $handler;
  protected $bUsed = true;

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

  public function validate() {

    $bResult = false;

    if (!$this->getValue()) {

      if (!$this->read('optional', false)) {

        $this->addMessage($this->translate("The field '%s' must be filled", $this->read('title')), $this->asAlias());
      }
      else {

        $this->isUsed(false);
        $bResult = true;
      }
    }
    else if ($this->getValue() === self::EMPTY_VALUE) {

      $this->isUsed(true);
      $bResult = true;
      $this->setValue('');
    }
    else {

      if (!$bResult = $this->validateFormat()) {

        $this->addMessage($this->translate("The field '%s' is not valid", $this->read('title')), $this->asAlias());
      }
    }

    return $bResult;
  }

  abstract protected function validateFormat();

  public function asAlias() {

    return array('alias' => $this->read('alias'));
  }

  public function escape() {

    if ($this->getValue()) {

      $sResult = "'".addslashes($this->getValue())."'";
    }
    else {

      $sResult = $this->read('default');
    }

    return $sResult;
  }
}

