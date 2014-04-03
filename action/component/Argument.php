<?php

namespace sylma\action\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Argument extends Named implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->allowText(true);
    $this->allowForeign(true);
  }

  public function asArray() {

    $aResult = array();
    $window = $this->getWindow();

    if (!$sSource = $this->readx('@source')) {

      $sSource = 'arguments';
    }

    $default = $this->getx('action:default');
    $arguments = $window->getVariable($sSource);
    $name = $this->loadName();

    if ($default) {

      $if = $window->createCondition($window->createNot($arguments->call('read', array($name, false))));
      $content = $window->parse($this->parseComponentRoot($default));
      $if->addContent($arguments->call('set', array($name, $content)));

      $aResult[] = $if;
    }
    else {

      $aArguments = array($name);

      if ($this->readx('@optional')) {

        $aArguments[] = false;
      }

      $aResult[] = $window->createInstruction($arguments->call('read', $aArguments));
    }

    $replace = $this->getx('action:replace');

    if ($replace && !$default) {

      $if = $window->createCondition($arguments->call('read', array($name, false)));
      $content = $window->parse($this->parseComponentRoot($replace));
      $if->addContent($arguments->call('set', array($name, $content)));

      $aResult[] = $if;
    }

    return $aResult;
  }

}

