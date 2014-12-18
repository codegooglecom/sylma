<?php

namespace sylma\template\binder\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\template;

class _Include extends Basic implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);

    return $this->build();
  }

  protected function getContext() {

    $parser = $this->getParser();
    return $parser::CONTEXT_JS;
  }

  protected function build() {

    $file = $this->getSourceFile($this->readx());

    $window = $this->getPHPWindow();
    $contexts = $window->getVariable('contexts');

    $callFile = $window->addControler(self::FILE_MANAGER)->call('getFile', array((string) $file));

    return array($contexts->call('get', array($this->getContext()))->call('add', array($callFile))->getInsert());
  }

  public function asArray() {

    $aResult = array();

    if (!$this->bBuilded) {

      $this->bBuilded = true;
      $aResult = $this->build();
    }

    return $aResult;
  }
}

