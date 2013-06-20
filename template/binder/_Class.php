<?php

namespace sylma\template\binder;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\template, sylma\parser\languages\js;

class _Class extends Basic implements common\arrayable {

  const CONTEXT_ALIAS = 'js/binder/context';
  const JS_OBJECTS_PATH = 'sylma.ui.tmp';

  protected $object;
  protected $element;
  protected $bRoot = false;

  protected $sExtend = '';
  protected $sID;
  protected $bAdded = false;

  public function parseRoot(dom\element $el) {

    $this->setNode($el, true);
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
    $this->startLog("Class [{$this->getExtend()}]");

    $this->getElement()->parseRoot($this->cleanAttributes($this->getNode()));

    $this->getParser()->stopObject();
    $this->stopLog();

    //$this->addToWindow();
  }

  protected function setObject(common\_object $obj) {

    $this->object = $obj;
  }

  protected function getObject() {

    if (!$this->object) {

      $this->launchException('No object defined');
    }

    return $this->object;
  }

  public function isRoot($bVal = null) {

    if (is_bool($bVal)) $this->bRoot = $bVal;

    return $this->bRoot;
  }

  protected function init() {

    $obj = $this->getWindow()->createObject();

    $this->setObject($obj);
    //$bName = (bool) $this->readx('@js:name');

    $this->loadID();

    $sParent = $this->readx('@js:parent');

    if ($this->isRoot()) {

      if (!$sParent) $sParent = self::JS_OBJECTS_PATH;
    }
    else if ($sParent) {

      $this->throwException(sprintf('@attribute parent must only appears on root element'));
    }

    $this->setExtend($this->readx('@js:class'));
    $obj->setProperty('Extends', $this->getWindow()->createVariable($this->getExtend()));
    //$this->setExtend($this->readx('@js:class'));

    //$obj->setProperty('name', $bName);
  }

  protected function setExtend($sExtend) {

    $this->sExtend = $sExtend;
  }

  public function getExtend() {

    return $this->sExtend;
  }

  public function setProperty($sName, $val) {

    $this->getObject()->setProperty($sName, $val);
  }

  protected function loadID() {

    $sID = uniqid('sylma');
    $this->setID($sID);

    return $this->getID();
  }

  protected function setID($sID) {

    $this->sID = $sID;
  }

  public function getID() {

    return $this->sID;
  }

  public function setEvent($sName, js\basic\instance\_Object  $val, template\element $el) {

    if ($el !== $this->getElement()) {

      $el->addToken('class', $sName);
      $val->setProperty('node', $sName);
    }

    $this->getObject()->setProperty('events.' . $sName, $val);
  }

  public function setMethod($sName, js\basic\instance\_Object $val) {

    $this->getObject()->setProperty($sName, $val);
  }

  public function asArray() {

    $obj = $this->getObject();

    if (!$this->bAdded) {

      if (count($obj->getProperties()) > 1) {

        $this->setExtend('sylma.binder.classes.' . $this->getID());

        $container = $this->getParser()->getContainer();
        $class = $this->getWindow()->createObject(array(), 'Class');
        $new = $this->getWindow()->createInstanciate($class, array($obj));
        $container->setProperty($this->getID(), $new);
      }

      $this->bAdded = true;
    }

    $obj = $this->loadSimpleComponent('object');
    $obj->setClass($this);

    $obj->parseRoot($this->getNode());
    $aResult[] = $obj->asArray();

    return $aResult;
  }
}

