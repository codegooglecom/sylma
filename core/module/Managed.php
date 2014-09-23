<?php

namespace sylma\core\module;
use \sylma\core;

class Managed extends Namespaced {

  protected $manager;
  protected $aManagers = array();

  /**
   * @deprecated use setManager instead
   */
  public function setControler($controler, $sName = '') {

    return $this->setManager($controler, $sName);
  }

  /**
   * @deprecated use getManager instead
   */
  public function getControler($sName = '', $bDebug = true) {

    return $this->getManager($sName, $bDebug);
  }

  protected function setManager($manager, $sName = '') {

    if ($sName) {

      $this->aManagers[$sName] = $manager;
    }
    else {

      $this->manager = $manager;
    }

    return $manager;
  }

  protected function getManager($sName = '', $bDebug = true) {

    $result = null;

    if ($sName) {

      $result = $this->loadManager($sName, $bDebug);
    }
    else {

      if ($bDebug && !$this->manager) {

        $this->throwException('No controler defined');
      }

      $result = $this->manager;
    }

    return $result;
  }

  protected function loadManager($sName, $bDebug = true) {

    $result = null;

    if (array_key_exists($sName, $this->aManagers)) {

      $result = $this->aManagers[$sName];
    }
    else {

      $result = \Sylma::getManager($sName, $bDebug);
    }

    return $result;
  }

  protected function setManagers(array $aManagers) {

    $this->aManagers = $aManagers;
  }

  protected function getManagers() {

    return $this->aManagers;
  }
}

