<?php

namespace sylma\device\test\samples;
use sylma\core, sylma\device;

class Builder extends device\Windower {

  protected function setupDevice() {

    parent::setupDevice();

    if ($sHeader = $this->read('header', false)) {

      device\test\Handler::setDevice($sHeader);
    }
  }
}
