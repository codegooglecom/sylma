<?php

namespace sylma\action\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class _Array extends reflector\component\Foreigner implements common\arrayable {

  const PREFIX = 'action';

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
  }

  public function asArray() {

    $window = $this->getWindow();

    return array($window->argToInstance($this->parseChildren($this->getNode()->getChildren())));
  }

  protected function addParsedChild(dom\element $el, array &$aResult, $mContent) {

    if ($sKey = $el->readx('@action:name', array(), false)) {

      $aResult[$sKey] = $mContent;
    }
    else {

      $aResult[] = $mContent;
    }
  }
}

