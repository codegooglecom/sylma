<?php

namespace sylma\core\argument;
use sylma\core;

class Manager extends core\module\Filed implements core\factory {

  //protected static $sArgumentClass = '\sylma\core\argument\Iterator';
  //protected static $sArgumentFile = 'core/argument/Iterator.php';

  public function __construct(core\argument $arguments = null) {

    $this->setDirectory(__FILE__);

    if (!$arguments) $arguments = 'manager.yml';
    $this->setArguments($arguments);
  }

  public function getClassName($sClass) {

    $sClass = 'classes/' . $sClass . '/name';
    
    return $this->readArgument($sClass);
  }
}
