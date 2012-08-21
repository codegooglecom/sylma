<?php

namespace sylma\core\window\classes;
use sylma\core, sylma\storage\fs, sylma\parser;

require_once('core/module/Controled.php');
require_once('core/window/action.php');

class Context extends core\module\Controled implements core\window\action {

  protected $action;
  protected $sContext;

  public function __construct(core\Initializer $controler) {

    $this->setControler($controler);
  }

  public function setAction(parser\action $action, $sContext = '') {

    $this->action = $action;
    $this->sContext = $sContext;
  }

  protected function getContext() {

    return $this->sContext;
  }

  protected function getAction() {

    return $this->action;
  }

  protected function setHeader($sContext) {

    $init = $this->getControler('init');

    $init->setHeaderContent($init->getMime($sContext));
  }

  public function asString() {

    $this->setHeader($this->getContext());

    $result = $this->getAction()->getContext($this->getContext());

    return $result->asString();
  }
}