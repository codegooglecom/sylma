<?php

namespace sylma\action\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class _Array extends reflector\component\Foreigner implements common\arrayable {

  const PREFIX = 'action';

  public function parseRoot(dom\element $el) {

    $this->setNode($el);

    $this->allowForeign(true);
  }

  public function asArray() {

    $window = $this->getWindow();

    $sExplode = $this->readx('@explode', false);

    if ($sExplode) {

      $result = explode($sExplode, $this->readx());
    }
    else {

      $aContent = $this->parseChildren($this->getNode()->getChildren());
      $result = array($window->argToInstance($aContent));
    }

    return $result;
  }

  protected function addParsedChild(dom\element $el, array &$aResult, $mContent) {

    $mContent = $this->getWindow()->toString($mContent);

    if ($sKey = $el->readx('@action:name', array(), false)) {

      $aResult[$sKey] = $mContent;
    }
    else {

      $aResult[] = $mContent;
    }
  }
}

