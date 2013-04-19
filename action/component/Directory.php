<?php

namespace sylma\action\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Directory extends reflector\component\Foreigner implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->setDirectory($this->getParser()->getSourceDirectory());
  }

  public function asArray() {

    $fs = $this->getWindow()->addControler('fs');
    $dir = $this->getDirectory($this->readx(''));

    return array($fs->call('getDirectory', array((string) $dir), '\sylma\storage\fs\directory'));
  }
}

