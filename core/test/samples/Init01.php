<?php

namespace sylma\core\test\samples;
use sylma\core;

class Init01 extends core\Initializer {

  protected $aGET = array();

  public function loadSettings($sServer, $sSylma) {

    $result = parent::loadSettings($sServer, $sSylma);

    \Sylma::setSettings($result);
    return $result;
  }

  protected function startSession() {


  }

  public function setGET($sPath, array $aArguments = array()) {

    $this->aGET = array(
      'path' => $sPath,
      'arguments' => $aArguments,
    );
  }

  protected function loadGET() {

    return $this->aGET;
  }
  }

