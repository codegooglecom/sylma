<?php

namespace sylma\parser\reflector\builder;
use \sylma\core, sylma\dom, sylma\parser\reflector;

abstract class Logger extends core\module\Domed {

  protected $bLog = false;
  protected $logger;

  protected function loadLogger() {

    $this->logger = $this->create('logger');
  }

  protected function loadLog(dom\document $doc = null) {

    if (!$doc || $doc->readx('@debug', array(), false)) {

      $this->getLogger()->asMessage();
    }
  }

  public function setLogger(reflector\logger\Logger $logger) {

    $this->logger = $logger;
  }

  protected function getLogger() {

    if (!$this->logger) {

      $this->launchException('No logger available');
    }

    return $this->logger;
  }

  /**
   * Cannot log anymore
   */
  protected function clearLogger() {

    $this->logger = null;
  }

  public function log($component, $sMessage, array $aVars = array()) {

    $this->startComponentLog($component, $sMessage, $aVars);
    $this->stopComponentLog();
  }

  public function startComponentLog($component, $sMessage = '', array $aVars = array()) {

    $this->getLogger()->startComponent($component, $sMessage, $aVars);
  }

  public function stopComponentLog() {

    $this->getLogger()->stopComponent();
  }
}

