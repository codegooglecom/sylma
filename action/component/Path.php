<?php

namespace sylma\action\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Path extends reflector\component\Foreigner implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $file = $this->getRoot()->getFile();
    $this->setFile($file);
  }

  public function asArray() {

    $request = $this->create('request', array($this->getFile()));

    return array($request->asString());
  }
}

