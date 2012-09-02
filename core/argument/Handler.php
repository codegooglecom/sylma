<?php

namespace sylma\core\argument\cached;
use sylma\core, sylma\parser, sylma\storage\fs;

\Sylma::load('/parser/Handler.php');

\Sylma::load('/core/argument');

class Handler extends parser\Handler implements core\argument {

  public function __construct(fs\file $file, core\argument $parent) {

    $this->setControler(\Sylma::getControler('argument'));

    $this->setFile($file);
    $this->setParent($parent);

    $this->setArguments($this->load());
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

  public function get($sPath = '', $bDebug = true) {

    return $this->getArguments()->get($sPath, $bDebug);
  }

  public function read($sPath = '', $bDebug = true) {

    return $this->getArguments()->read($sPath, $bDebug);
  }

  public function set($sPath = '', $mValue = null) {

    return $this->getArguments()->set($sPath, $mValue);
  }

  public function setParent(core\argument $parent) {

    return $this->getArguments()->setParent($parent);
  }

  public function getParent() {

    return $this->getArguments()->getParent();
  }

  public function normalize() {

    return $this->getArguments()->normalize();
  }

  public function asArray() {

    return $this->getArguments()->asArray();
  }

}
