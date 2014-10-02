<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\php, sylma\parser\languages\common, sylma\template as template_ns;

class _Switch extends Unknowned implements common\arrayable, template_ns\parser\component {

  protected $reflector;

  public function parseRoot(dom\element $el) {

    $this->setNode($el, true);
    $this->allowText(true);
  }

  protected function setReflector(php\basic\_Switch $reflector) {

    $this->reflector = $reflector;
  }

  protected function getReflector() {

    return $this->reflector;
  }

  protected function build() {

    $window = $this->getWindow();

    $test = $this->getTemplate()->getPather()->parseExpression($this->readx('@test'), true);
    $result = $window->createSwitch($window->toString($test));
    $aCases = array();

    foreach ($this->queryx('*') as $el) {

      $aContent = $this->parseChildren($el->getChildren());
      $aCases[$el->readx('@value', array(), false)] = $window->parse($aContent);
    }

    $result->setContents($aCases);

    return $result;
  }

  public function asArray() {

    return array($this->build());
  }
}

