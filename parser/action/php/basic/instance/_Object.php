<?php

namespace sylma\parser\action\php\basic\instance;
use \sylma\parser\action\php, sylma\core;

require_once(dirname(__dir__) . '/Controled.php');
require_once(dirname(dirname(__dir__)) . '/_object.php');
require_once(dirname(dirname(__dir__)) . '/_instance.php');
require_once('core/argumentable.php');

class _Object extends php\basic\Controled implements php\_object, php\_instance {

  private $interfaceObject;

  public function __construct(php\_window $controler, php\basic\_Interface $interface) {

    $this->setControler($controler);
    $this->setInterface($interface);
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

  public function asArgument() {

    return $this->getControler()->createArgument(array('instance' => 'object'));
  }
}