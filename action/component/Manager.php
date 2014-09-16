<?php

namespace sylma\action\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Manager extends reflector\component\Foreigner implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
  }

  public function asArray() {

    $manager = $this->getWindow()->getSylma()->call('getManager', array($this->readx('@name')));

    if ($sCall = $this->readx('@call', false)) {

      $result = $manager->call($sCall);
    }
    else {

      $result = $manager;
    }

    return array($result);
  }
}

