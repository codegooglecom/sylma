<?php

namespace sylma\template\binder;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\template;

class _Class extends Basic implements common\arrayable, common\argumentable {

  const CONTEXT_ALIAS = 'js/binder/context';
  const JS_OBJECTS_PATH = 'sylma.ui.tmp';

  protected $object;
  protected $element;
  protected $bRoot = false;

  protected $sExtend = '';

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->allowComponent(true);
  }

  public function setElement(template\element $element) {

    $this->element = $element;

    $this->build();
  }

  public function getElement() {

    return $this->element;
  }

  protected function loadName() {

    $this->sName = uniqid('sylma');

    return $this->sName;
  }

  protected function build() {

    $this->init();

    $this->getParser()->startObject($this);
    $this->getElement()->parseRoot($this->cleanAttributes($this->getNode()));
    $this->getParser()->stopObject();

    //$this->addToWindow();
  }

  protected function setObject(common\_object $obj) {

    $this->object = $obj;
  }

  public function isRoot($bVal = null) {

    if (is_bool($bVal)) $this->bRoot = $bVal;

    return $this->bRoot;
  }

  protected function init() {

    $obj = $this->getWindow()->createObject();

    $this->setObject($obj);
    $bName = (bool) $this->readx('@js:name');

    $sID = $this->loadID();

    $sParent = $this->readx('@js:parent');

    if ($this->isRoot()) {

      if (!$sParent) $sParent = self::JS_OBJECTS_PATH;
    }
    else if ($sParent) {

      $this->throwException(sprintf('@attribute parent must only appears on root element'));
    }

    $this->setExtend($this->readx('@js:class'));
    $obj->setProperty('name', $bName);

    $container = $this->getParser()->getContainer();
    $container->setProperty($sID, $obj);
  }

  protected function setExtend($sExtend) {

    $this->sExtend = $sExtend;
  }

  public function getExtend() {

    return $this->sExtend;
  }

  protected function getObject() {

    if (!$this->object) {

      $this->launchException('No object defined');
    }

    return $this->object;
  }

  public function setProperty($sName, $val) {

    $this->getObject()->setProperty($sName, $val);
  }

/*
  protected function parseComponent(dom\element $el) {

    $result = parent::parseComponent($el);

    if ($result instanceof component\Property) {

      $this->setProperty($result->getName(), $result->getValue());
    }
    else if ($result instanceof _Class) {

      $this->setObject($result->getName(), $result);
    }
    else if ($result instanceof component\Event) {

      $this->setEvent($result->getID(), $result);
    }
    else {

      $this->launchException('Unknown component class', get_defined_vars());
    }

    return $result;
  }
*/
  protected function addToWindow() {

    $this->getParser()->addToWindow($this->getObject());
    //$context = $window->addContext(self::CONTEXT_ALIAS);
    //$context->add()

    //return 'myclass : {}';
  }

  public function setEvent($sName, $val) {

    $this->getObject()->setProperty('events.' . $sName, $val);
  }

  public function asArray() {

    $obj = $this->loadSimpleComponent('object');
    $obj->setClass($this);

    $obj->parseRoot($this->getNode());

    return $obj->asArray();
  }

  public function asArgument() {
$this->launchException('usefull ?');
    $obj = $this->getWindow()->createObject();

    return $obj;
  }
}

