<?php

namespace sylma\core\module\test\samples;
use sylma\core;

require_once('core/module/Filed.php');

class Filed extends core\module\Filed {

  public function __construct() {

    $this->setDirectory(__file__);
  }

  public function create($sName, array $aArguments = array(), $sDirectory = '') {

    return parent::create($sName, $aArguments, $sDirectory);
  }

  public function createArgument($mArguments, $sNamespace = '') {

    return parent::createArgument($mArguments, $sNamespace);
  }

  public function getDirectory($sPath = '', $bDebug = true) {

    return parent::getDirectory($sPath, $bDebug);
  }

  public function getFile($sPath = '', $bDebug = true) {

    return parent::getFile($sPath, $bDebug);
  }

  public function loadControler($sName) {

    return parent::loadControler($sName);
  }

  public function setArguments($mArguments = null, $bMerge = true) {

    return parent::setArguments($mArguments, $bMerge);
  }

  public function setDirectory($mDirectory) {

    parent::setDirectory($mDirectory);
  }
}