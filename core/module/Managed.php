<?php

namespace sylma\core\module;
use \sylma\core;

class Managed extends Controled {

  public function setManager($controler, $sName = '') {

    return parent::setControler($controler, $sName);
  }

  public function getManager($sName = '', $bDebug = true) {

    return parent::getControler($sName, $bDebug);
  }
}