<?php

namespace sylma\parser\action\php\basic;
use \sylma\core, \sylma\parser\action\php;

require_once(dirname(__dir__) . '/_var.php');

require_once('Controled.php');

abstract class _Var extends Controled implements php\_var {

  private $sName = '';
  protected $instance;

  public function __construct($controler, php\_instance $instance, $sName) {

    $this->setName($sName);
    $this->setControler($controler);
    $this->setInstance($instance);
  }

  protected function getInstance() {

    return $this->instance;
  }

  protected function setInstance(php\_instance $instance) {

    $this->instance = $instance;
  }

  protected function setName($sName) {

    $this->sName = $sName;
  }

  protected function getName() {

    return $this->sName;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'var' => array(
        '@name' => $this->sName,
      ),
    ));
  }
}