<?php

namespace sylma\parser\caller;
use sylma\core, sylma\parser\action\php;

require_once('core/module/Controled.php');

class Method extends core\module\Controled {

  protected $sName;
  protected $sReturn;
  protected $aArguments = array();

  public function __construct(core\factory $controler, $sName, $sReturn, array $aArguments = array()) {

    $this->setControler($controler);

    $this->sName = $sName;
    $this->sReturn = $sReturn;
    $this->aArguments = $aArguments;
  }

  public function getReturn() {

    return $this->sReturn;
  }

  public function getName() {

    return $this->sName;
  }

  public function reflectCall(php\_window $window, php\basic\_ObjectVar $var, array $aArguments = array()) {

    $result = $window->createCall($var, $this->getName(), $window->stringToInstance($this->getReturn()), $aArguments);

    return $result;
  }
}