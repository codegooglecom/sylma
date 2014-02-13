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

    $test = $this->parseTest();
    $result = $window->createCondition($test, $this->parseContent());

    return array($result);
  }

  protected function parseContent() {

    $content = $this->getParser()->parseContent($this->getNode()->getChildren());

    return $content;
  }

  protected function parseTest() {

    $window = $this->getWindow();

    $return = $this->createDummy('dummy', array(), null, false);
    $manager = $window->addManager('device', null, $return);
    $sValue = $this->readx('@test', true);

    if ($sValue{0} === '!') {

      $result = $window->createNot($manager->call('isDevice', array(substr($sValue, 1))));
    }
    else {

      $result = $manager->call('isDevice', array($sValue));
    }

    return $result;
  }
}
