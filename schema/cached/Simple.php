<?php

namespace sylma\schema\cached;
use sylma\core;

class Simple extends Basic {

  protected $sValue;

  public function __construct($sValue, array $aSettings = array()) {

    $this->setValue($sValue);
    $this->setSettings($aSettings);
  }

  public function setValue($sValue) {

    $this->sValue = $sValue;
  }

  public function getValue() {

    return $this->sValue;
  }

  public function validate() {

    $bResult = false;

    if (is_null($this->getValue()) || $this->getValue() === '') {

      if (!$this->read('optional', false)) {

        $this->addMessage($this->translate("The field '%s' must be filled", $this->read('title')), array('alias' => $this->read('alias')));
      }
      else {

        $bResult = true;
      }
    }
    else {

      $bResult = is_string($this->getValue());
    }

    return $bResult;
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

