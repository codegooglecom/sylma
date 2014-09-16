<?php

namespace sylma\parser\reflector\builder;
use \sylma\core, sylma\dom, sylma\parser\reflector;

abstract class Logger extends core\module\Domed {

  protected $logger;

  protected function useLog() {

    return \Sylma::read('debug/parser');
  }

  protected function loadLogger() {

    if ($this->useLog()) $this->logger = $this->create('logger');
  }

  protected function loadLog(dom\document $doc = null) {

    if ($this->useLog() && (!$doc || $doc->readx('@debug', array(), false))) {

      $this->getLogger()->asMessage();
    }
  }

  public function setLogger(reflector\logger\Logger $logger) {

    $this->logger = $logger;
  }

  /**
   * @return reflector\logger\Logger
   */
  protected function getLogger($bDebug = true) {

    if (!$this->logger) {

      if ($bDebug) $this->launchException('No logger available');
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

    if ($this->useLog()) $this->getLogger()->startComponent($component, $sMessage, $aVars);
  }

  public function stopComponentLog() {

    if ($this->useLog()) $this->getLogger()->stopComponent();
  }
}

