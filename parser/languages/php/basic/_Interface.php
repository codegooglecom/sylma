<?php

namespace sylma\parser\languages\php\basic;
use \sylma\parser\languages\common, \sylma\parser\languages\php;

\Sylma::load('/parser/languages/common/basic/Controled.php');

class _Interface extends common\basic\Controled {

  protected $sName = '';
  protected $reflection;
  protected $sFile = '';

  public function __construct(common\_window $window, $sInterface, $sFile = '') {

    $this->setControler($window);
    $this->setName($sInterface);
    $this->sFile = $sFile;
  }

  public function getName($bRelative = false) {

    return $this->sName;
  }

  protected function setName($sInterface) {

    if (!preg_match('/^[\w_\\\]*$/', $sInterface)) {

      $this->getControler()->throwException(sprintf('Invalid class name : %s', $sInterface));
    }
    else if (!$sInterface) {

      $this->getControler()->throwException('Empty name not allowed');
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

      $this->getControler()->throwException(sprintf('No reflector implemented, cannot find @class %s', $this->getName()));
    }

    return $reflection->implementsInterface($sInterface);
  }
}