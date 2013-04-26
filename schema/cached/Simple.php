<?php

namespace sylma\schema\cached;
use sylma\core;

class Simple extends Basic {

  protected $sValue;
  protected $handler;

  public function __construct(Form $handler, core\argument $schema = null) {

    $this->setHandler($handler);
    if ($schema) $this->setSettings($schema);
  }

  protected function setHandler(Form $handler) {

    $this->handler = $handler;
  }

  protected function getHandler() {

    return $this->handler;
  }

  public function validate() {


  }

  public function escape($sValue) {

    return "'".addslashes($sValue)."'";
  }
}

