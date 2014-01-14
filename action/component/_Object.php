<?php

namespace sylma\action\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class _Object extends Caller implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->allowForeign(true);
  }

  public function asArray() {

/*
    if ($sFile = $this->readx('@file')) {

      $file = $this->getSourceFile($sFile);
    }
*/
    $sClass = $this->getWindow()->getAbsoluteClass($this->readx('@class', true), (string) $this->getSourceDirectory());

    return array($this->createObject($sClass, $this->loadArguments(), null, false));
  }
}

