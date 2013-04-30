<?php

namespace sylma\view\parser\builder;
use sylma\core, sylma\parser\reflector, sylma\parser\languages\common;

abstract class Variabled extends reflector\handler\Documented {

  protected function prepareFormed(common\_window $window) {

    $this->prepareArgumented($window);
    $window->checkVariable('post', get_class($this->create('argument')));

    return $window;
  }

  protected function prepareArgumented(common\_window $window) {

    $window->createVariable('aSylmaArguments', 'php-array');
    $window->checkVariable('arguments', get_class($this->create('argument')));

    return $window;
  }

}

