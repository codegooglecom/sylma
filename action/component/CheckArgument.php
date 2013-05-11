<?php

namespace sylma\action\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class CheckArgument extends reflector\component\Foreigner {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->allowText(true);
    $this->allowForeign(true);

    $content = $this->build();
    $this->getWindow()->add($content);
  }

  protected function build() {

    $window = $this->getWindow();

    if (!$sSource = $this->readx('@source')) {

      $sSource = 'arguments';
    }

    $default = $this->getx('self:default');
    $arguments = $window->getVariable($sSource);

    if ($default) {

      $result = $window->createCondition($window->createNot($arguments->call('read', array($this->readx('@name'), false))));

      $content = $this->parseComponentRoot($default);
      $result->addContent($arguments->call('set', array($this->readx('@name'), $content)));
    }
    else {

      $result = $arguments->call('read', array($this->readx('@name')));
    }

    return array($result);
  }

}

