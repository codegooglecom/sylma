<?php

namespace sylma\template\binder;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class _Object extends Basic implements common\arrayable {

  const JS_LOAD_CONTEXT = 'js-load';
  const PARENT_PATH = 'sylma.ui.tmp';

  protected $class;
  protected $var;
  protected $sJSClass;
  protected $sName;
  protected $sParent;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);

    $this->loadID();
    $this->loadName();
    $this->loadParentName();
  }

  public function setClass(_Class $class) {

    $this->class = $class;
  }

  protected function loadName() {

    if (!$sName = $this->readx('@js:name', false)) {

      $sName = uniqid('sylma');
    }

    $this->setName($sName);
    return $this->getName();
  }

  protected function setName($sName) {

    $this->sName = $sName;
  }

  protected function getName() {

    return $this->sName;
  }

  protected function getClass() {

    return $this->class;
  }

  protected function getJSClass() {

    return $this->sJSClass;
  }

  protected function setJSClass($sName) {

    $this->sJSClass = $sName;
  }

  public function setSubObject($sName, $val) {

    //$this->getObject()->setProperty('objects.' . $sName, $val);
    //$this->aObjects[$sName] = $obj;
  }
/*
  protected function getParent() {

    return $this->parent;
  }

  protected function setParent(self $parent) {

    $this->parent = $parent;
  }
*/
  protected function isRoot() {

    return $this->getClass()->isRoot();
  }

  protected function setVar(common\_var $obj) {

    $this->var = $obj;
  }

  protected function getVar() {

    if (!$this->var) {

      $this->launchException('No var defined');
    }

    return $this->var;
  }

  protected function loadParentName() {

    if (!$sParent = $this->readx('@js:parent')) {

      $sParent = self::PARENT_PATH;
    }

    $this->setParentName($sParent);
    return $this->getParentName();
  }

  protected function getParentName() {

    return $this->sParent;
  }

  protected function setParentName($sParent) {

    $this->sParent = $sParent;
  }

  protected function addToWindow() {

    $window = $this->getParser()->getPHPWindow();
    $parent = $this->getParser()->getObjects();
    $context = $window->getVariable('contexts')->call('get', array(self::JS_LOAD_CONTEXT), '\sylma\core\argument', true);

    $aContent = array('sylma.ui.load(', array($this->getParentName(), ', ', $parent->call('asJSON', array(), 'php-string'), ')'));
    $window->add($context->call('add', array($window->createString($aContent))));
  }

  protected function buildObject() {
//dsp($this->isRoot());
    if ($this->isRoot()) {

      $var = $this->getParser()->getObjects();
      $sPath = $this->getName();
    }
    else {
//$this->launchException('test');
      $parent = $this->getParser()->getObject();
      //$parent->setSubObject($this->getID(), $this);
//dsp($parent);
      $var = $parent->getVar();
      $sPath = 'objects.' . $this->getName();
    }
//dsp($var);

    $arg = $var->call('set', array(
      $sPath,
      array(
        'extend' => $this->getClass()->getExtend(),
        'binder' => $this->getClass()->getID(),
        'id' => $this->getID(),
      ),
      true,
    ), '\sylma\core\argument', true);
//dsp($this->isRoot());
    $this->setVar($arg);
  }

  public function setOption($sName, $val) {

    $this->getVar()->call('set', array('options.' . $sName, $val), 'php-boolean', true);
    //$this->aProperties[$sName] = $val;
  }

  public function asArray() {

//dsp('start');
    $this->buildObject();
    $this->getParser()->startObject($this);


    $element = $this->getClass()->getElement();
    $element->setAttribute('id', $this->getID());

    $aElement = $this->getParser()->getPHPWindow()->parseArrayables(array($element));

    if ($this->isRoot()) $this->addToWindow();

//dsp('stop');
    $this->getParser()->stopObject();

    return array(
      $aElement,
    );
  }

}

