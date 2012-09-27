<?php

namespace sylma\parser\languages\php\basic\instance;
use \sylma\parser\languages\common, \sylma\parser\languages\php, sylma\core;

\Sylma::load('../../../common/basic/Controled.php', __DIR__);
require_once('parser/languages/common/_object.php');
require_once('parser/languages/common/_instance.php');
require_once('core/argumentable.php');

class _Object extends common\basic\Controled implements common\_object, common\_instance {

  private $interfaceObject;

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

  public function asArgument() {

    return $this->getControler()->createArgument(array('instance' => 'object'));
  }
}