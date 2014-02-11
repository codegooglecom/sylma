<?php

namespace sylma\device\component;
use sylma\core, sylma\parser\reflector, sylma\parser\languages\common;

class Assert extends reflector\component\Foreigner implements common\arrayable {

  public function parseRoot(\sylma\dom\element $el) {

    $this->setNode($el);

    $this->allowForeign(true);
    $this->allowUnknown(true);
    $this->allowText(true);
  }

  public function asArray() {

    $window = $this->getWindow();
    $manager = $window->addManager('device');

    $test = $manager->call('isDevice', array($this->readx('@test', true)));
    $result = $window->createCondition($test, $this->parseContent());

    return array($result);
  }

  protected function parseContent() {

    $window = $this->getWindow();
    $content = $this->getParser()->parseContent($this->getNode()->getChildren());

    return $content;
  }
}
