<?php

namespace sylma\core\argument\test\controler;
use sylma\core\argument\test, sylma\core, sylma\storage\fs, sylma\dom, sylma\modules\tester;

class Filed extends tester\Basic implements test\controler {

  protected $parent;

  public function __construct(test\Basic $parent, fs\directory $dir) {

    $this->setDirectory($dir);
    $this->setControler($parent);
  }

  public function createArgument($mArguments = array(), $sNamespace = '') {

    if (is_string($mArguments)) {

      $mArguments = (string) $this->getFile($mArguments);
    }

    if ($sNamespace) $aNS = array($sNamespace);
    else $aNS = array();

    return $this->create($this->readArgument('class-alias'), array($mArguments, $aNS));
  }

  public function getDirectory($sPath = '', $bDebug = true) {

    return parent::getDirectory();
  }

  public function setArguments($mArguments = null, $bMerge = true) {

    parent::setArguments($mArguments, $bMerge);
  }

  public function getArguments() {

    return parent::getArguments();
  }

  public function get($sPath, $bDebug = true) {

    return $this->getArguments()->get($sPath);
  }

  public function set($sPath, $mVar = null) {

    return $this->getArguments()->set($sPath, $mVar);
  }

  public function getFile($sPath = '', $bDebug = true) {

    return parent::getFile($sPath, $bDebug);
  }
}