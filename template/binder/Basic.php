<?php

namespace sylma\template\binder;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Basic extends reflector\component\Foreigner {

  protected $sID;

  protected function getID() {

    return $this->sID;
  }

  protected function setID($sID) {

    $this->sID = $sID;
  }

  protected function loadID() {

    if (!$sID = $this->readx('@id')) {

      $sID = uniqid('sylma');
    }

    $this->setID($sID);

    return $this->getID();
  }

/*
  protected function getProperties() {

    return $this->aProperties;
  }
*/


/*
  protected function getObjects() {

    return $this->aObjects;
  }
/*
  protected function getCollections() {

    return $this->aCollections;
  }
*/

}

