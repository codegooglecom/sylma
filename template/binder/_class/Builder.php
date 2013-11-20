<?php

namespace sylma\template\binder\_class;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template, sylma\parser\languages\js;

class Builder extends Proped implements common\arrayable, template\binder\_class {

  /**
   * @var template\element
   */
  protected $element;

  /**
   * TRUE if an object extending this class is added
   */
  protected $bUsed = false;

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

  protected function build() {

    $this->init();

    $this->getParser()->startObject($this);
    $this->startLog("Class [{$this->getExtend()}]");

    $this->getElement()->parseRoot($this->cleanAttributes($this->getNode()));

    $this->getParser()->stopObject(false);
    $this->stopLog();

    //$this->addToWindow();
  }

  protected function init() {

    $obj = $this->getWindow()->createObject();

    $this->setObject($obj);
    //$bName = (bool) $this->readx('@js:name');

    $this->loadID();

    $sParent = $this->loadParent();

    if ($this->isRoot()) {

      if (!$sParent) $sParent = self::JS_OBJECTS_PATH;
    }
    else if ($sParent) {

      $this->throwException('@attribute parent must only appears on root element');
    }

    $this->setExtend($this->readx('@js:class'));

    $obj->setProperty('Extends', $this->getWindow()->createVariable($this->getExtend()));

    if ($sParentName = $this->loadParentName()) {

      $obj->setProperty('sylma.parentName', $sParentName);
    }
    //$this->setExtend($this->readx('@js:class'));

    //$obj->setProperty('name', $bName);
  }

  protected function isUsed($bValue = null) {

    if (is_bool($bValue)) $this->bUsed = $bValue;

    return $this->bUsed;
  }

  public function addTo(common\_object $container) {

    $js = $this->getObject();

    if ($this->useTemplate()) {

      $js->setProperty('buildTemplate', $this->template);
    }

    if ($this->isUsed()) {

      $this->loadExtend();

      $class = $this->getWindow()->createObject(array(), 'Class');
      $new = $this->getWindow()->createInstanciate($class, array($js));

      $container->setProperty($this->getID(), $new);
    }
  }

  /**
   * @uses _Object::setClass() parseRoot() and asArray()
   */
  public function asArray() {

    $this->isUsed(true);

    $aResult = array();

    $obj = $this->loadSimpleComponent('object');
    $obj->setClass($this);

    $obj->parseRoot($this->getNode());
    $aResult[] = $obj->asArray();

    return $aResult;
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
}

