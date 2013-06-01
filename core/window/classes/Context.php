<?php

namespace sylma\core\window\classes;
use sylma\core, sylma\parser\action, sylma\core\window;

class Context extends core\module\Argumented implements window\action {

  protected $action;
  protected $sContext;

  public function __construct(core\Initializer $controler) {

    $this->setControler($controler);
  }

  public function setAction(action\handler $action, $sContext = '') {

    $this->action = $action;
    $this->sContext = $sContext;

    $parser = $this->getControler('action');
    /*
    $this->getAction()->setContexts($this->createArgument(array(
      $sContext => $parser->createContext(),
    )));
    */
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

    $sResult = '';
    $this->setHeader($this->getContext());

    if ($result = $this->getAction()->getContext($this->getContext())) {

      $sResult = $result->asString();
    }

    return $sResult;
  }
}
