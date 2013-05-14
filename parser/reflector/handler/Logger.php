<?php

namespace sylma\parser\reflector\handler;

class Logger extends Parsed {

  protected $logger;

  protected function loadLogger() {

    $this->logger = $this->create('logger');
  }

  protected function loadLog() {

    $this->getLogger()->asMessage();
  }

  protected function getLogger() {

    return $this->logger;
  }

  public function startComponentLog($component, $sMessage = '', array $aVars = array()) {

    $this->getLogger()->startComponent($component, $sMessage, $aVars);
  }

  public function stopComponentLog() {

    $this->getLogger()->stopComponent();
  }
}

