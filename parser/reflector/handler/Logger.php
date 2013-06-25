<?php

namespace sylma\parser\reflector\handler;
use \sylma\core, sylma\parser\reflector;

abstract class Logger extends Parsed {

  protected $logger;

  protected function log($sMessage, array $aVars = array()) {

    $this->getRoot()->log($this, $sMessage, $aVars);
  }

  public function startComponentLog($component, $sMessage = '', array $aVars = array()) {

    $this->getRoot()->startComponentLog($component, $sMessage, $aVars);
  }

  public function stopComponentLog() {

    $this->getRoot()->stopComponentLog();
  }
}

