<?php

namespace sylma\action\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Argument extends reflector\component\Foreigner implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
  }

  public function asArray() {

    $window = $this->getWindow();

    if (!$sSource = $this->readx('@source')) {

      $sSource = 'arguments';
    }

    $arguments = $window->getVariable($sSource);

    $aArguments = array($this->readx('@name'));
    if ($this->readx('@optional')) $aArguments[] = false;
    $argument = $arguments->call('read', $aArguments);

    return $this->readx('@escape') ? array("'", $window->callFunction('addslashes', 'php-string', array($argument)), "'") : array($argument);
  }
}

