<?php

namespace sylma\schema\cached;
use sylma\core;

class Basic extends core\module\Argumented {

  protected $handler;

  public function setHandler(Form $handler) {

    $this->handler = $handler;
  }

  protected function getHandler() {

    return $this->handler;
  }

  protected function addMessage($sMessage, array $aArguments = array()) {

    $this->getHandler()->addMessage($sMessage, $aArguments);
  }
}

