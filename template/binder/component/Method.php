<?php

namespace sylma\template\binder\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Method extends reflector\component\Foreigner implements common\arrayable {

  protected $sName;
  protected $sValue;

  public function parseRoot(dom\element $el) {

    $window = $this->getWindow();

    $this->loadValue($el);
    $this->loadID();

    $function = $window->createFunction(array('e'), $this->getValue());
    $sName = $el->readAttribute('name');

    $event = $window->createObject();

    $event->setProperty('name', $sName);
    $event->setProperty('callback', $function);

/*
    if (!$this->elementIsObject($el->getParent())) {

      $sClass = uniqid('sylma');

      $this->getParent()->getLastElement()->addToken('class', $sClass);
      $event->setProperty('target', $sClass);
    }
*/
    $this->getParser()->getObject()->setMethod($this->getID(), $event, $this->getRoot()->getCurrentElement());
  }

  protected function loadValue(dom\element $el) {

    $this->sValue = $this->parseContent($el->read());
  }

  protected function getValue() {

    return $this->sValue;
  }

  protected function setName($sName) {

    $this->sName = $sName;
  }

  public function getName() {

    return $this->sName;
  }

  protected function parseContent($sContent) {

    $aReplaces = array(
      '/%([\w-_]+)%/' => '\$(this).retrieve(\'sylma-$1\')',
      '/%([\w-_]+)\s*,\s*([^%]+)%/' => '\$(this).store(\'sylma-$1\', $2)');

    $sResult = preg_replace(array_keys($aReplaces), $aReplaces, $sContent);

    return $sResult;
  }

  public function asArray() {

    return array();
  }
}

