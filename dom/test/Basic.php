<?php

namespace sylma\dom\test;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs;

class Basic extends tester\Basic implements core\argumentable {

  const NS = 'http://www.sylma.org/dom/test';
  protected $sTitle = 'DOM';

  public function __construct() {

    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');

    $this->setArguments('settings.yml');

    $this->setControler($this);
  }

  public function getFile($sPath = '', $bDebug = true) {

    return parent::getFile($sPath, $bDebug);
  }

  public function createDocument($mContent = null) {

    return parent::createDocument($mContent);
  }
}

