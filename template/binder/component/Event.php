<?php

namespace sylma\template\binder\component;
use sylma\core, sylma\dom, sylma\template\binder;

class Event extends Method {

  protected $sID;
  protected $sValue;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->loadID();

    $this->build();
  }

  protected function build() {

    if ($class = $this->getObject(false)) {

      $this->addToObject($class);
      $this->isBuilt(true);
    }
  }

  protected function addToObject(binder\Basic $target) {

    if ($target instanceof binder\_class) {

      $this->addToClass($target);
    }
    else {

      $target->setEvent($this->getID());
      $this->addToClass($target->getClass(), true);
    }
  }

  protected function addToClass(binder\_class $target, $bOptional = false) {

    $window = $this->getWindow();

    $this->loadValue($this->getNode());

    $function = $window->createFunction(array('e'), $this->getValue());
    $this->loadName();

    $obj = $window->createObject();

    $obj->setProperty('name', $this->getName());
    $obj->setProperty('callback', $function);

    if ($bOptional) {

      $obj->setProperty('optional', true);
    }

    $target->setEvent($this->getID(), $obj, $this->getRoot()->getCurrentElement());
  }

  protected function loadID() {

    $sName = uniqid('sylma');
    $this->sID = $sName;
  }

  public function getID() {

    return $this->sID;
  }

  public function asArray() {

    //if (!$this->isBuilt()) {

      $this->addToObject($this->getObject());
    //}

    return array();
  }
}

