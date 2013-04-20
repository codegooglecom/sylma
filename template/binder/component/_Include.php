<?php

namespace sylma\template\binder\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\template;

class _Include extends Basic implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setFile($this->getSourceFile($el->read()));

    $window = $this->getPHPWindow();
    $contexts = $window->getVariable('contexts');

    $callFile = $window->addControler(self::FILE_MANAGER)->call('getFile', array((string) $this->getFile()));
    $window->add($contexts->call('get', array($this->getContext()))->call('add', array($callFile)));
  }

  protected function getContext() {

    $parser = $this->getParser();
    return $parser::CONTEXT_JS;
  }

  public function asArray() {

    return array();
  }
}

