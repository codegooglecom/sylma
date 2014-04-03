<?php

namespace sylma\action\component;
use sylma\core, sylma\dom, sylma\parser\languages\common;

class GetArgument extends Named implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->allowForeign(true);
  }

  protected function build() {

    $window = $this->getWindow();

    if (!$sSource = $this->readx('@source')) {

      $sSource = 'arguments';
    }

    $arguments = $window->getVariable($sSource);

    $name = $this->loadName();

    if ($name) {

      $aArguments = array($name);

      if ($this->readx('@optional')) {

        $aArguments[] = false;
      }

      $sFunction = $this->readx('@format') == 'array' ? 'query' : 'read';
      $argument = $arguments->call($sFunction, $aArguments);
    }
    else {

      $argument = $arguments->call('shift');
    }

    return $this->readx('@escape') ? $this->reflectEscape($argument) : array($argument);
  }

  public function asArray() {

    return $this->build();
  }
}

