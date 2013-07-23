<?php

namespace sylma\template\binder\component;
use sylma\core, sylma\dom, sylma\template\binder;

class Event extends Method {

  protected $sID;
  protected $sValue;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->addToObject($this->getParser()->getObject());
  }

  protected function addToObject(binder\_Class $target) {

    $window = $this->getWindow();

    $this->loadValue($this->getNode());
    $this->loadID();

    $function = $window->createFunction(array('e'), $this->getValue());
    $this->loadName();

    $obj = $window->createObject();

    $obj->setProperty('name', $this->getName());
    $obj->setProperty('callback', $function);

/*
    if (!$this->elementIsObject($el->getParent())) {

      $sClass = uniqid('sylma');

      $this->getParent()->getLastElement()->addToken('class', $sClass);
      $event->setProperty('target', $sClass);
    }
*/
    $target->setEvent($this->getID(), $obj, $this->getRoot()->getCurrentElement());
  }

  protected function loadID() {

    $sName = uniqid('sylma');
    $this->sID = $sName;
  }

  public function getID() {

    return $this->sID;
  }
}

