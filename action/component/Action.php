<?php

namespace sylma\action\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Action extends Caller implements common\arrayable {

  const PREFIX = 'action';

  public function parseRoot(dom\element $el) {

    parent::parseRoot($el);
    $this->getWindow()->addControler('action');
  }

  public function asArray() {

    $fs = $this->getWindow()->addControler('action');
    $path = $this->loadPath($this->readx('@path'));
    $aArguments = $this->loadArguments();

    return array($fs->call('getAction', array((string) $path->asFile(true), $aArguments, null, false))->call('asString'));
  }
}

