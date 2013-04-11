<?php

namespace sylma\view\parser;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\storage\fs, sylma\parser\languages\common;

class Builder extends reflector\handler\Documented {

  protected $sMode;
  protected $reflector;

  const FORM_ARGUMENTS = 'form.xml';
  const VIEW_ARGUMENTS = 'view.xml';

  public function build() {

    $this->setDirectory(__FILE__);
  }

  protected function getMode() {

    return $this->sMode;
  }

  protected function setMode($sMode) {

    $this->sMode = $sMode;
  }

  protected function getReflector() {

    return $this->reflector;
  }

  protected function setReflector(Elemented $reflector) {

    $this->reflector = $reflector;
  }

  protected function loadSelfTarget(fs\file $file, $sMode = '') {

    if ($sMode) {

      $result = $this->getManager()->getCachedFile($file, ".{$sMode}.php");
    }
    else {

      $result = parent::loadSelfTarget($file);
    }

    return $result;
  }

  public function getResultFile($sMode = '') {

    // TODO : TMP
    return $this->loadSelfTarget($this->getFile(), $sMode);
  }

  protected function createReflector() {

    $result = parent::createReflector();
    //$result->setMode($this->getMode()); // actually made by self::parseReflector() with elemented::parseRoot()
    $this->setReflector($result);

    return $result;
  }

  protected function parseReflector(reflector\domed $reflector, dom\document $doc) {

    return $reflector->parseRoot($doc->getRoot(), $this->getMode());
  }

  protected function reflectView(dom\document $doc, common\_window $window, $bForm = false, $sMode = '') {

    $file = $this->getFile();

    if ($bForm) $this->setArguments(self::FORM_ARGUMENTS);
    else $this->setArguments(self::VIEW_ARGUMENTS);

    $this->setMode($sMode);

    $result = $this->reflectMain($file, $doc, $window);

    return $result;
  }
}

