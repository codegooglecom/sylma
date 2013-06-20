<?php

namespace sylma\action\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class _String extends reflector\component\Foreigner implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->allowText(true);
    $this->allowForeign(true);
  }

  public function asArray() {

    $window = $this->getWindow();
    $content = $window->parseArrayables($this->parseChildren($this->getNode()->getChildren()));

    return array($window->createCast($window->createString($content)));
  }
}

