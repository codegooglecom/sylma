<?php

namespace sylma\action\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class GetArgument extends reflector\component\Foreigner implements common\arrayable {

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
    $sNamed = $this->readx('@format') == 'array' ? 'query' : 'read';
    $argument = $this->readx('@name') ? $arguments->call($sNamed, $aArguments) : $arguments->call('shift');

    return $this->readx('@escape') ? $this->reflectEscape($argument) : array($argument);
  }
}

