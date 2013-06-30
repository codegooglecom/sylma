<?php

namespace sylma\action\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class _Boolean extends reflector\component\Foreigner implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);

    $this->allowForeign(true);
  }

  public function asArray() {

    $window = $this->getWindow();

    if ($this->getNode()->isComplex()) {

      $content = $window->createCast($window->argToInstance($this->parseChildren($this->getNode()->getChildren())), 'bool');
    }
    else {

      $sVal = $this->readx('@value', true);
      $content = $sVal === 'false' || $sVal === '0' ? false : true;
    }

    return array($content);
  }
}

