<?php

namespace sylma\parser\action\php\basic;
use \sylma\parser\action\php;

require_once('Controled.php');

class _Interface extends Controled {

  protected $sName = '';
  protected $reflection;
  protected $sFile = '';

  public function __construct(php\_window $window, $sInterface, $sFile = '') {

    $this->setControler($window);
    $this->setName($sInterface);
    $this->sFile = $sFile;
  }

  public function getName() {

    return $this->sName;
  }

  protected function setName($sInterface) {

    if (!preg_match('/^[\w_\\\]*$/', $sInterface)) {

      $this->getControler()->throwException(txt('Invalid class name : %s', $sInterface));
    }

    $this->sName = $sInterface;
  }

  public function getFile() {

    return $this->sFile;
  }

  protected function getReflection() {

    return $this->reflection;
  }

  protected function loadReflection() {

    if (!$this->reflection) {

      $factory = \Sylma::getControler('factory');
      $factory::includeClass($this->getName(), $this->getFile());

      $this->reflection = new \ReflectionClass($this->getName());
    }
  }

  public function isInstance($sInterface) {

    if ($this->getName() == $sInterface) return true;

    $this->loadReflection();

    if (!$reflection = $this->getReflection()) {

      $this->getControler()->throwException(txt('No reflector implemented, cannot find @class %s', $this->getName()));
    }

    return $reflection->implementsInterface($sInterface);
  }
}