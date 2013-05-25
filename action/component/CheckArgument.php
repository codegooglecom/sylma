<?php

namespace sylma\action\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class CheckArgument extends reflector\component\Foreigner implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->allowText(true);
    $this->allowForeign(true);
  }

  public function asArray() {

    $window = $this->getWindow();

    if (!$sSource = $this->readx('@source')) {

      $sSource = 'arguments';
    }

    $default = $this->getx('self:default');
    $arguments = $window->getVariable($sSource);
    $sName = $this->readx('@name');

    if ($default) {

      $result = $window->createCondition($window->createNot($arguments->call('read', array($this->readx('@name'), false))));

      $content = $this->parseComponentRoot($default);

      $result->addContent($arguments->call('set', array($sName, $content)));
    }
    else {

      $result = $arguments->call('read', $sName);
    }

    return array($result);
  }

}

