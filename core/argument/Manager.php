<?php

namespace sylma\core\argument;
use sylma\core;

\Sylma::load('/core/module/Argumented.php');
\Sylma::load('/core/factory.php');

class Manager extends core\module\Filed implements core\factory {

  //protected static $sArgumentClass = '\sylma\core\argument\Iterator';
  //protected static $sArgumentFile = 'core/argument/Iterator.php';

  public function __construct() {

    $this->setDirectory(__FILE__);
    $this->setArguments('manager.yml');
  }
}
