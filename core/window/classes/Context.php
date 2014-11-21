<?php

namespace sylma\core\window\classes;
use sylma\core, sylma\core\window;

class Context extends core\module\Argumented {

  protected $sContext;

  public function __construct(core\Initializer $controler) {

    $this->setControler($controler);
  }

  protected function getContext() {

    return $this->sContext;
  }

  protected function setHeader($sContext) {

    $init = $this->getControler('init');

    $init->setHeaderContent($init->getMime($sContext));
  }
}
