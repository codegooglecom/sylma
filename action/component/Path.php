<?php

namespace sylma\action\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Path extends reflector\component\Foreigner implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->loadDefaultArguments();
  }

  public function asArray() {
//return array($this->create('request', array($this->getRoot()->getFile()))->asString());
    if ($sValue = $this->readx()) {

      $request = $this->create('request', array($sValue, $this->getSourceDirectory()));
    }
    else {

      $request = $this->create('request');
      $request->setFile($this->getRoot()->getFile());
    }

    return array($request->asString());
  }
}

