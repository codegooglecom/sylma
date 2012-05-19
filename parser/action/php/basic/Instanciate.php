<?php

namespace sylma\parser\action\php\basic;
use sylma\parser\action\php, sylma\core;

require_once('Controled.php');

require_once('parser/action/php/_object.php');
require_once('parser/action/php/linable.php');
require_once('core/argumentable.php');

class Instanciate extends Controled implements php\_object, php\_instance, php\linable, core\argumentable {

  protected $instance;

  public function __construct(php\_window $controler, php\_instance $instance, array $aArguments = array()) {

    $this->setControler($controler);

    $this->instance = $instance;
    $this->aArguments = $aArguments;
  }

  public function getInterface() {

    return $this->instance->getInterface();
  }

  protected function getArguments() {

    return $this->aArguments;
  }

  public function addContent($mVar) {

    return $this->getControler()->addContent($mVar);
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'instanciate' => array(
        '@class' => $this->getInterface()->getName(),
        '#argument' => $this->getArguments(),
    )));
  }
}