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

    $sValue = $this->readx('');

    if (substr($sValue, 0, 2) === '//') {

      $result = $this->createDummy('distant', array($sValue));
    }
    else {

      $fs = $this->getWindow()->addControler('fs');
      $file = $this->getSourceFile($sValue);

      $result = $fs->call('getFile', array((string) $file), '\sylma\storage\fs\file');
    }

    return array($result);
  }

  public function asArgument() {

    return current($this->asArray())->asArgument();
  }
}

