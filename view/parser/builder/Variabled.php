<?php

namespace sylma\view\parser\builder;
use sylma\core, sylma\parser\reflector, sylma\parser\languages\common;

abstract class Variabled extends reflector\handler\Documented {

  protected function prepareFormed() {

    $window = $this->prepareArgumented();
    $window->checkVariable('post', get_class($this->create('argument')));

    return $window;
  }

  protected function prepareArgumented() {

    $window = $this->createWindow();
    $window->createVariable('aSylmaArguments', 'php-array');
    $window->checkVariable('arguments', get_class($this->create('argument')));

    return $window;
  }

}

