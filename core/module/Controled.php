<?php

namespace sylma\core\module;
use \sylma\core;

/**
 * @deprecated must use Managed's class methods
 */
class Controled extends Namespaced implements core\controled {

  protected $controler;
  protected $aControlers = array();

  public function getControlers() {

    return $this->aControlers;
  }

  public function setControler($controler, $sName = '') {

    if ($controler === $this) {

      //$this->throwException('Cannot use controler as himself');
    }

    if ($sName) $this->aControlers[$sName] = $controler;
    else $this->controler = $controler;
  }

  public function getControler($sName = '', $bDebug = true) {

    $result = null;

    if ($sName) {

      $result = $this->loadControler($sName);
    }
    else {

      if ($bDebug && !$this->controler) {

        $this->throwException('No controler defined');
      }

      $result = $this->controler;
    }

    return $result;
  }

  protected function loadControler($sName) {

    $controler = null;

    if (array_key_exists($sName, $this->aControlers)) {

      $controler = $this->aControlers[$sName];
    }
    else {

      $controler = \Sylma::getControler($sName);
    }

    return $controler;
  }
/*
  protected function getNamespace($sPrefix = null) {

    $sNamespace = parent::getNamespace($sPrefix);

    if (!$sNamespace && $this->getControler('', false)) {

      $sNamespace = $this->getControler()->getNamespace($sPrefix);
    }

    return $sNamespace;
  }
*/
}