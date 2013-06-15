<?php

namespace sylma\schema\cached\form;
use sylma\core;

class Simple extends Basic {

  protected $sValue;

  public function __construct($sValue, array $aSettings = array()) {

    $this->setValue($sValue);
    $this->setSettings($aSettings);
  }

  public function setValue($sValue = '') {

    $this->sValue = $sValue;
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

        $bResult = true;
      }
    }
    else {

      if (!$bResult = is_string($this->getValue())) {

        $this->addMessage($this->translate("The field '%s' is not valid", $this->read('title')), $this->asAlias());
      }
    }

    return $bResult;
  }

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
