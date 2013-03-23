<?php

namespace sylma\core\module\test;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs;

class Basic extends tester\Prepare {

  const NS = 'http://www.sylma.org/core/module/test';
  protected $sTitle = 'Module';

  public function __construct() {

    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');

    $this->setArguments('settings.yml');

    $this->setControler($this);
  }

  public function getArgument($sPath, $bDebug = true, $mDefault = null) {

    return parent::getArgument($sPath, $bDebug, $mDefault);
  }

  public function setArgument($sPath, $mValue) {

    return parent::setArgument($sPath, $mValue);
  }
}

