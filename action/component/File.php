<?php

namespace sylma\action\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class File extends reflector\component\Foreigner implements common\arrayable, common\argumentable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->setDirectory($this->getParser()->getSourceDirectory());

    $this->getWindow()->addControler('fs');
  }

  public function asArray() {

    $fs = $this->getWindow()->addControler('fs');
    $file = $this->getSourceFile($this->readx(''));

    return array($fs->call('getFile', array((string) $file), '\sylma\storage\fs\file'));
  }

  public function asArgument() {

    return current($this->asArray())->asArgument();
  }
}

