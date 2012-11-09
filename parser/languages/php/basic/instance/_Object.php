<?php

namespace sylma\parser\languages\php\basic\instance;
use \sylma\parser\languages\common, \sylma\parser\languages\php, sylma\core;

class _Object extends common\basic\Controled implements common\_object, common\scope, common\_instance {

  private $interfaceObject;
  protected $bStatic = false;

  public function __construct(common\_window $controler, php\basic\_Interface $interface) {

    $this->setControler($controler);
    $this->setInterface($interface);
  }

  public function getInstance() {

    return $this;
  }

  public function getInterface() {

    return $this->interfaceObject;
  }

  public function addContent($mVar) {

    return $this->getControler()->add($mVar);
  }

  public function setInterface(php\basic\_Interface $interface) {

    $this->interfaceObject = $interface;
  }
}