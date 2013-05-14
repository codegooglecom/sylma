<?php

namespace sylma\parser\reflector\handler;
use \sylma\core, sylma\parser\reflector;

class Logger extends Parsed {

  protected $logger;

  public function startComponentLog($component, $sMessage = '', array $aVars = array()) {

    $this->getRoot()->startComponentLog($component, $sMessage, $aVars);
  }

  public function stopComponentLog() {

    $this->getRoot()->stopComponentLog();
  }
}

