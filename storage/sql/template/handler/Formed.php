<?php

namespace sylma\storage\sql\template\handler;
use sylma\core, sylma\dom, sylma\storage\sql\template;

class Formed extends Pather {

  protected $reflector;

  public function parseRoot(dom\element $el) {

    parent::parseRoot($el);

    $this->loadReflector();
  }

  protected function loadReflector() {

    $window = $this->getWindow();

    $sClass = $this->getFactory()->findClass('reflector')->read('name');
    $instance = $window->tokenToInstance($sClass);

    $this->reflector = $window->addVar($window->createInstanciate($instance, array()));
  }

  public function getReflector() {

    return $this->reflector;
  }
}

