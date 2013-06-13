<?php

namespace sylma\template\binder\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Event extends Method {

  protected $sID;
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
    $this->getParser()->getObject()->setEvent($this->getID(), $event, $this->getRoot()->getCurrentElement());
  }

  protected function loadID() {

    $sName = uniqid('sylma');
    $this->sID = $sName;
  }

  public function getID() {

    return $this->sID;
  }
}

