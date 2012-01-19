<?php

namespace sylma\parser\action\php\basic;
use \sylma\parser\action\php;

require_once('Controled.php');
require_once(dirname(__dir__) . '/_object.php');
require_once(dirname(__dir__) . '/_instance.php');

class ObjectInstance extends Controled implements php\_object, php\_instance {

  private $interfaceObject;

  public function __construct(Window $controler, _Interface $interface) {

    $this->setControler($controler);
    $this->setInterface($interface);
  }

  public function getInterface() {

    return $this->interfaceObject;
  }

  public function addContent($mVar) {

    return $this->getControler()->add($mVar);
  }

  public function setInterface(_Interface $interface) {

    $this->interfaceObject = $interface;
  }
}