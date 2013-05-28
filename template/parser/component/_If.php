<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\php, sylma\parser\languages\common, sylma\template as template_ns;

class _If extends Unknowned implements common\arrayable, template_ns\parser\component {

  protected $reflector;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    //$this->allowForeign(true);
    //$this->allowUnknown(true);
    $this->allowText(true);
  }

  public function asArray() {

    $this->setReflector($this->getWindow()->createCondition());
    
    $test = $this->getTemplate()->getPather()->parseExpression($this->readx('@test'));
    $if = $this->getReflector();

    $aChildren = $this->parseChildren($this->getNode()->getChildren());

    $if->setTest($test);
    $if->setContent($aChildren);

    return array($if);
  }

  protected function setReflector(php\basic\Condition $if) {

    $this->reflector = $if;
  }

  protected function getReflector() {

    return $this->reflector;
  }

  protected function parseComponent(dom\element $el) {

    $result = parent::parseComponent($el);

    if ($result instanceof _Else) {

      $this->getReflector()->addElse($this->getWindow()->toString($result->parseContent()));
      $result = null;
    }

    return $result;
  }
}

