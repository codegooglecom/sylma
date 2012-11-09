<?php

namespace sylma\core\argument\parser;
use sylma\core, sylma\parser, sylma\storage\fs;

class Handler extends parser\Handler implements core\argument {

  public function __construct(fs\file $file, core\argument $parent = NULL, core\factory $manager = NULL) {

    if (!$manager) $manager = \Sylma::getControler('argument');
    $this->setControler($manager);

    $this->setFile($file);
    $this->setBaseDirectory($file->getParent());

    if ($parent) $this->setParent($parent);

    $this->setArguments($this->load());
  }

  public function &locateValue(array &$aPath = array(), $bDebug = true, $bReturn = false) {

    return $this->getArguments()->locateValue(&$aPath, $bDebug, $bReturn);
  }

  public function current() {

    return $this->getArguments()->current();
  }

  public function key() {

    return $this->getArguments()->key();
  }

  public function next() {

    return $this->getArguments()->next();
  }

  public function rewind() {

    return $this->getArguments()->rewind();
  }

  public function valid() {

    return $this->getArguments()->valid();
  }

  public function add($mValue) {

    return $this->getArguments()->add($mValue);
  }

  public function query() {

    return $this->getArguments()->query();
  }

  public function get($sPath = '', $bDebug = true) {

    return $this->getArguments()->get($sPath, $bDebug);
  }

  public function read($sPath = '', $bDebug = true) {

    return $this->getArguments()->read($sPath, $bDebug);
  }

  public function set($sPath = '', $mValue = null) {

    return $this->getArguments()->set($sPath, $mValue);
  }

  public function merge($mArgument) {

    return $this->getArguments()->merge($mArgument);
  }

  public function setParent(core\argument $parent) {

    return $this->getArguments()->setParent($parent);
  }

  public function getParent() {

    return $this->getArguments()->getParent();
  }

  public function normalize($iMode = self::NORMALIZE_DEFAULT) {

    return $this->getArguments()->normalize($iMode);
  }

  public function asArray($bEmpty = false) {

    return $this->getArguments()->asArray();
  }

}
