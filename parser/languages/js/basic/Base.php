<?php

namespace sylma\parser\languages\js\basic;
use sylma\parser\languages\common;

abstract class Base extends common\basic\Controled {

  protected $aProperties = array();
  protected $sInterface;

  public function __construct(common\_window $window, $sInterface) {

    $this->setControler($window);
    $this->setInterface($sInterface);
  }

  public function getInterface() {

    return $this->sInterface;
  }

  public function setInterface($sClass) {

    $this->sInterface = $sClass;
  }

  protected function setProperties(array $aValues) {

    $this->aProperties = $aValues;
  }

  public function getProperty($sName, $bDebug = true) {

    $result = null;

    if (array_key_exists($sName, $this->aProperties)) {

      $result = $this->aProperties[$sName];
    }
    else if ($bDebug) {

      $this->getControler()->throwException(sprintf('Property %s does not exists', $sName));
    }

    return $result;
  }

  public function setProperty($sName, $value) {

    $this->aProperties[$sName] = $value;
    return $value;
  }

  protected function getProperties() {

    return $this->aProperties;
  }
}
